# API — Transações recorrentes

Endpoints de `Modules/RecurringTransaction` (`RecurringTransactionController`): modelos que geram transações automaticamente, opcionalmente ligados a uma dívida ou meta. Convenções gerais em [README.md](README.md). Contexto de negócio (gerador, normalização mensal) em [../architecture/modules/recurring-transaction.md](../architecture/modules/recurring-transaction.md).

Todos os endpoints exigem `auth:sanctum` e passam pelo middleware `setlocale`.

Regras comuns:

- Todas as ações passam por `ApiController::safe()`. `ValidationException` é relançada (422 no formato Laravel). Qualquer outra exceção devolve `fail($e->getMessage(), $e, $e->getCode())`. Na prática, `ModelNotFoundException` (id inexistente ou soft-deleted) e `AuthorizationException` (policy negada) têm código 0 e devolvem **500**, não 404/403.
- Os erros de validação dos FormRequests deste módulo usam um formato próprio (sempre JSON, mesmo sem `Accept`): `{"success": false, "message": "Corrige os campos indicados e tenta novamente.", "errors": {"campo": ["..."]}}`, com mensagens em `recurringtransaction::validation`. O handler global atual acrescenta `data:null` a essa resposta de exceção na API. Os erros de categoria lançados pelo repository (`SelectableCategory::lockAndValidate`) também seguem o handler global descrito em [convenções](README.md).
- As respostas de sucesso das ações (exceto `index`) têm `message: ""`.

## Formato `RecurringTransactionResource`

Usado em `store`, `show`, `update`, `toggle` e `destroy`:

```json
{
  "id": 14,
  "title": "Renda",
  "type": "expense",
  "amount": 650,
  "nextOccurrence": "2026-12-01",
  "description": "Renda do apartamento",
  "frequency": "monthly",
  "startDate": "2026-01-01",
  "endDate": null,
  "lastGeneratedAt": "2026-11-01",
  "isActive": true,
  "isLinked": false,
  "linkedType": null,
  "linked": null,
  "account": {
    "id": 1,
    "name": "Ordenado",
    "currencyId": 1,
    "type": "bank_account",
    "balance": 1250.5,
    "active": true
  },
  "category": {
    "id": "30",
    "name": "Habitação",
    "typeTranslated": "Despesa",
    "type": "expense",
    "icon": "Home",
    "color": "#6366f1",
    "isDefault": true,
    "isArchived": false,
    "canManage": false,
    "canDelete": false
  }
}
```

- `customFrequencyDays` só aparece quando `frequency = "custom"`.
- `nextOccurrence` é `null` quando passa de `endDate`.
- `linkedType` é `debt`, `financial_goal` ou `null`. `linked` é o `DebtResource` ou o `FinancialGoalResource` da entidade ligada (ver [debts.md](debts.md) / [financial-goals.md](financial-goals.md)), ou `null`.
- `account` (`AccountResource`), `category` (`CategoryResource`) e `transactions` (lista de `TransactionResource`, ver [transactions.md](transactions.md)) só aparecem quando a relação está carregada. Isso depende do endpoint (indicado em cada um).
- `transactions_count` só aparece quando a contagem é carregada. Nenhum endpoint atual a carrega.

---

### `GET /api/v1/recurrings`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

`RecurringTransactionPaginate` restringe por membership nas contas e exclui soft-deleted. Filtros: type, category_id, is_active (1/0), frequency, account_id, user_id, start_date[gte/lte], end_date[gte/lte]. Aliases anteriores categoryId, isActive, accountId, userId, startDate, endDate continuam a traduzir para campos canónicos.

Pesquisa: título, descrição ou nome da conta. Ordenação: id, title, amount, frequency, start_date, is_active e next_occurrence (próxima data calculada pelo calendário, null após end_date). Flags seguem RecurringTransactionPolicy.

`meta.categories` acompanha âmbito/filtros/pesquisa, removendo a categoria selecionada. `includeSummary=1` adiciona `meta.summary`, global a todas as contas visíveis e independente da página/filtros, com activeCount, pausedRecurring, incomeCount, expenseCount, currencySummaries e upcoming limitado a cinco. Sem a flag não se calcula esse resumo.

---

### `POST /api/v1/recurrings`

Cria uma recorrente ativa e gera logo as ocorrências em atraso (backfill desde `start_date` até hoje, como transações `completed`). Tudo corre numa única transação DB: se a geração falhar, nada é criado. **Permissão:** creator da conta ou `createTransaction` (policy `AccountPolicy::storeTransaction`).

**Body** (`StoreRecurringTransactionRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `account_id` | `required\|exists:accounts,id` | Conta |
| `category_id` | `nullable\|integer\|exists:categories,id` + `SelectableCategory` | Categoria do mesmo `type` (opcional) |
| `type` | `required\|in:expense,revenue` | Tipo |
| `amount` | `required\|numeric\|min:0.01` | Montante |
| `title` | `required\|string\|max:255` | Título |
| `description` | `nullable\|string` | Notas. Se vazio, as transações geradas usam o `title` como descrição |
| `frequency` | `required\|in:daily,weekly,monthly,yearly,custom` | Frequência |
| `custom_frequency_days` | `required_if:frequency,custom\|nullable\|integer\|min:1` | Intervalo em dias (só `custom`) |
| `start_date` | `required\|date` | Primeira ocorrência |
| `end_date` | `nullable\|date\|after:start_date` | Última data possível |
| `is_linked` | `boolean` | Liga a uma dívida ou meta |
| `linked_type` | `nullable\|required_if:is_linked,true\|in:debt,financial_goal` | Tipo de ligação |
| `linked_id` | `nullable\|required_if:is_linked,true\|integer` | Id da dívida/meta |

Notas sobre a ligação:
- `linked_type`/`linked_id` são ignorados quando `is_linked` não é verdadeiro.
- A API não verifica se a dívida/meta existe nem se o utilizador lhe tem acesso. Isso só é validado pelos repositories de Debts/FinancialGoal durante a geração. Se a geração falhar, o pedido falha.
- Numa meta, `expense` gera contribuições (reservas) e `revenue` gera levantamentos.

```json
{
  "account_id": 1,
  "category_id": 30,
  "type": "expense",
  "amount": 650,
  "title": "Renda",
  "description": "Renda do apartamento",
  "frequency": "monthly",
  "start_date": "2026-01-01",
  "end_date": null,
  "is_linked": false
}
```

**Resposta 200:** envelope com `data` no formato `RecurringTransactionResource`. Inclui `transactions` (as ocorrências geradas neste pedido e as anteriores) e não inclui `account` nem `category`.

```json
{
  "success": true,
  "message": "",
  "data": {
    "id": 14,
    "title": "Renda",
    "type": "expense",
    "amount": 650,
    "nextOccurrence": "2026-12-01",
    "description": "Renda do apartamento",
    "frequency": "monthly",
    "startDate": "2026-01-01",
    "endDate": null,
    "lastGeneratedAt": "2026-11-01",
    "isActive": true,
    "isLinked": false,
    "linkedType": null,
    "linked": null,
    "transactions": [
      {
        "id": 400,
        "accountId": 1,
        "type": "expense",
        "typeTransalated": "Despesa",
        "amount": 650,
        "date": "2026-01-01",
        "description": "Renda do apartamento",
        "status": "completed",
        "statusTranslated": "Concluída",
        "categoryId": 30,
        "userId": 1
      }
    ]
  },
  "errors": null
}
```

**Erros:**
- 422 — validação (formato do módulo, ver acima).
- 422 — categoria indisponível revalidada no repository (formato Laravel).
- 403 — `UnauthorizedCreateTransactionException`, lançada na geração das transações.
- 500 — `AuthorizationException`: o role não tem `createTransaction`.
- 500 — o utilizador não é membro da conta: `AccountPolicy::isOwner(null)` dá TypeError (known-issues #11).
- 500 — erros da geração de pagamentos de dívida ou movimentos de meta, ou outro erro inesperado.

---

### `GET /api/v1/recurrings/{id}`

Detalhe de uma recorrente. **Permissão:** membro da conta (policy `show`).

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | Id da recorrente |

**Resposta 200:** envelope com `data` no formato `RecurringTransactionResource`, com `account`, `category` e `linked` carregados (exemplo no topo). Não inclui `transactions`.

**Erros:**
- 500 — `ModelNotFoundException`: recorrente inexistente ou eliminada.
- 500 — `AuthorizationException`: o utilizador não é membro da conta.

---

### `PUT|PATCH /api/v1/recurrings/{id}`

Atualiza os campos editáveis. `type`, `frequency`, `start_date`, `account_id` e a ligação não podem ser alterados. Não gera ocorrências. **Permissão:** creator da conta ou `editRecurringTransaction`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Body** (`UpdateRecurringTransactionRequest`, todos opcionais):

| Campo | Regras | Descrição |
|---|---|---|
| `category_id` | `nullable\|integer\|exists:categories,id` + `SelectableCategory` | Categoria do mesmo tipo da recorrente. A original é aceite mesmo que esteja arquivada |
| `amount` | `sometimes\|numeric\|min:0.01` | Montante |
| `description` | `nullable\|string` | Notas |
| `end_date` | `nullable\|date` | Data de fim (sem a regra `after:start_date` do `store`) |
| `custom_frequency_days` | `nullable\|integer\|min:1` | Intervalo em dias (aceite mesmo que `frequency` não seja `custom`) |
| `title` | `sometimes\|string\|max:255` | Título |

```json
{
  "amount": 675,
  "end_date": "2027-12-31",
  "title": "Renda (atualizada)"
}
```

**Resposta 200:** envelope com `data` no formato `RecurringTransactionResource`. Inclui `account`, que fica carregada pela verificação da policy, e não inclui `category` nem `transactions`.

**Erros:**
- 422 — validação (formato do módulo); categoria indisponível (formato Laravel).
- 500 — `AuthorizationException`: sem permissão.
- 500 — `ModelNotFoundException`.

---

### `DELETE /api/v1/recurrings/{id}`

Soft delete da recorrente. As transações já geradas mantêm-se. **Permissão:** creator da conta ou `destroyRecurringTransaction`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:** envelope com `data` no formato `RecurringTransactionResource` do registo eliminado (com `account`).

**Erros:**
- 500 — `AuthorizationException`: sem permissão.
- 500 — `ModelNotFoundException`.

---

### `POST /api/v1/recurrings/{id}/toggle`

Alterna `is_active` (ativa ↔ pausada). Não recebe body e não gera ocorrências em atraso no momento. O job diário retoma a geração, com backfill desde a última ocorrência gerada. **Permissão:** creator da conta ou `editRecurringTransaction` (policy `update`).

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:** envelope com `data` no formato `RecurringTransactionResource` (com `account`), com o novo `isActive`.

```json
{
  "success": true,
  "message": "",
  "data": {
    "id": 14,
    "title": "Renda",
    "type": "expense",
    "amount": 650,
    "nextOccurrence": "2026-12-01",
    "description": "Renda do apartamento",
    "frequency": "monthly",
    "startDate": "2026-01-01",
    "endDate": null,
    "lastGeneratedAt": "2026-11-01",
    "isActive": false,
    "isLinked": false,
    "linkedType": null,
    "linked": null,
    "account": {
      "id": 1,
      "name": "Ordenado",
      "currencyId": 1,
      "type": "bank_account",
      "balance": 1250.5,
      "active": true
    }
  },
  "errors": null
}
```

**Erros:**
- 500 — `AuthorizationException`: sem permissão.
- 500 — `ModelNotFoundException`.
