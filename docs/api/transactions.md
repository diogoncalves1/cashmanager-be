# API — Transações

Endpoints de transações de conta (`Modules/Accounts`, `TransactionController`): listagem, CRUD e confirmação de transações agendadas. Convenções gerais em [README.md](README.md).

Todos os endpoints exigem `auth:sanctum` e passam pelo middleware `setlocale`.

Regras comuns:

- Só transações `completed` mexem no saldo da conta (`accounts.balance`). Ver [../architecture/modules/accounts.md](../architecture/modules/accounts.md).
- `ValidationException` (incluindo as lançadas pelo repository, como a validação de categoria ou os bloqueios de metas) é relançada e devolve **422**, com campos `message`/`errors` e envelope adicional do handler global (ver convenções da API). As outras exceções devolvem `fail($e->getMessage(), $e, $e->getCode())`. As que têm código 500 ou 0 acabam em **500**.
- Transações ligadas a metas financeiras:
  - histórico de contribuições/levantamentos (`contribution`/`withdrawal`) → 422 `transaction_id`: "Gere este movimento histórico a partir da meta financeira…" (`historical_linked`);
  - despesa com utilizações de meta (`usage`) → 422 `transaction_id`: "Esta despesa está associada a uma meta…" (`linked`).

---

### `GET /api/v1/transactions`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

`TransactionPaginate` aplica pertença e viewTransaction na conta e exclui movimentos neutralizados das metas.

| Query | Efeito |
|---|---|
| status[eq], type[eq] | Estado e tipo |
| category_id[eq/in], account_id[eq/in], user_id[eq] | Categoria, conta e autor |
| date[gte], date[lte] | Intervalo YYYY-MM-DD, combinável |
| search | Descrição, nome da conta ou autor |
| order_by | id, account_id, date, amount, type, status |

Aliases: categoryId, accountId, userId/user, dateFrom/dateTo. Linhas usam TransactionListResource, com user/category/account/currency, valores formatados e goalUsages/hasGoalUsage. As flags edit/destroy/confirm são falsas para movimentos ligados a metas. `meta.stats` usa a mesma consulta filtrada/pesquisada sem paginação; `meta.categories` omite apenas a seleção de categoria.

---

### `POST /api/v1/transactions`

Cria uma transação. Se for `completed`, ajusta o saldo (+receita / −despesa). Regista `transaction_added` ou `transaction_scheduled` no activity log da conta. **Permissão:** `createTransaction` na conta.

**Body** (`TransactionRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `account_id` | `required\|exists:accounts,id` | Conta |
| `category_id` | `required\|integer\|exists:categories,id` + `SelectableCategory` | Categoria selecionável pelo utilizador, do mesmo `type` (não arquivada, não interna) |
| `amount` | `required\|numeric\|min:0` | Montante (positivo; o sinal vem do `type`) |
| `type` | `required\|string\|in:revenue,expense` | Tipo |
| `date` | `required\|date` | Data (`YYYY-MM-DD`) |
| `description` | `nullable\|string` | Descrição |
| `status` | `nullable\|in:pending,completed` | Estado. **Na prática é obrigatório** (ver nota) |
| `user_id` | `nullable\|exists:users,id` | Validado, mas ignorado: o autor é sempre o utilizador autenticado |

Regras de data:
- `completed` com data futura → erro;
- `pending` com data anterior a hoje → erro;
- `pending` com data de hoje é aceite.

Nota: o repository lê `$input['status']`. Se o campo não vier no body, gera um erro PHP ("Undefined array key") e devolve 500. Com `status: null`, o insert falha (coluna enum NOT NULL). Enviar sempre `pending` ou `completed`.

```json
{
  "account_id": 1,
  "category_id": 12,
  "amount": 35.2,
  "type": "expense",
  "date": "2026-11-15",
  "description": "Continente",
  "status": "completed"
}
```

**Resposta 200:**

```json
{
  "success": true,
  "message": "Transacção adicionada com sucesso!",
  "data": {
    "id": 381,
    "accountId": 1,
    "type": "expense",
    "typeTransalated": "Despesa",
    "amount": 35.2,
    "date": "2026-11-15",
    "description": "Continente",
    "status": "completed",
    "statusTranslated": "Concluída",
    "categoryId": 12,
    "userId": 1
  },
  "errors": null
}
```

A chave `typeTransalated` tem mesmo este erro ortográfico no `TransactionResource`.

**Erros:**
- 422 — validação do `TransactionRequest`. Categoria indisponível → `category_id`: "Esta categoria não está disponível para este movimento." (também revalidada dentro da transação DB).
- 403 — `UnauthorizedCreateTransactionException` (sem `createTransaction`).
- 404 — `AccountNotFoundException` (só se a conta desaparecer entre a validação e o repository).
- 500 — `InvalidTransactionPendingDateException`: combinação data/estado inválida (verificada **antes** da permissão).
- 500 — `status` em falta (ver nota acima).

---

### `GET /api/v1/transactions/{id}`

Detalhe de uma transação (a partir de `transactions_view`). **Permissão:** `viewTransaction`.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | Id da transação |

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": {
    "goalUsages": [
      { "id": "4", "name": "Férias", "amount": 20 }
    ],
    "id": 381,
    "date": "2026-11-15",
    "status": "completed",
    "statusTranslated": "Concluída",
    "amount": 35.2,
    "amountFormated": "35.20 €",
    "description": "Continente",
    "type": "expense",
    "typeTranslated": "Despesa",
    "userId": 1,
    "userName": "Diogo",
    "accountId": "1",
    "accountName": "Ordenado",
    "currencySymbol": "€",
    "currencyCode": "EUR",
    "category": {
      "id": "12",
      "name": "Supermercado",
      "typeTranslated": "Despesa",
      "type": "expense",
      "icon": "ShoppingCart",
      "color": "#22c55e",
      "isDefault": true,
      "isArchived": false,
      "canManage": false,
      "canDelete": false
    }
  },
  "errors": null
}
```

Aqui a chave é `typeTranslated` (bem escrita) e `accountId` é string.

**Erros:**
- 500 — `TransactionNotFoundException` (a exceção tem código 500, não 404). Também acontece se a transação não tiver categoria, porque `transactions_view` faz INNER JOIN em categorias.
- 500 — `UnauthorizedViewTransactionException` (código 500, devia ser 403).

---

### `PUT|PATCH /api/v1/transactions/{id}`

Edita montante, data, descrição e categoria. `type`, `status` e `account_id` são imutáveis. Se a transação estiver `completed`, aplica a diferença de montante ao saldo. Regista `transaction_updated` com as alterações. **Permissão:** `editTransaction`.

**Parâmetros de rota:** `id` (int, obrigatório) — id da transação.

**Body** (`TransactionUpdateRequest`, todos obrigatórios também no `PATCH`):

| Campo | Regras | Descrição |
|---|---|---|
| `amount` | `required\|numeric\|min:0` | Novo montante |
| `date` | `required\|date` | Nova data |
| `description` | `nullable\|string` | Descrição |
| `category_id` | `required\|integer\|exists:categories,id` + `SelectableCategory` | Categoria do mesmo tipo da transação. A categoria original é aceite mesmo que esteja arquivada ou pertença a outro membro |

```json
{
  "amount": 40,
  "date": "2026-11-15",
  "description": "Continente + Pingo Doce",
  "category_id": 12
}
```

**Resposta 200:** envelope com `message` `"Transacção actualizada com sucesso!"` e `data` no formato `TransactionResource` (igual ao do `POST`).

Nota: não há validação de data contra o estado. A verificação `InvalidTransactionDateException` só corre se o pedido trouxer `status=completed`, que não faz parte das regras. Uma transação `completed` pode passar para uma data futura, e uma `pending` para o passado.

**Erros:**
- 422 — validação; categoria indisponível; transação ligada a metas (`historical_linked` / `linked`).
- 403 — `UnauthorizedUpdateTransactionException`.
- 500 — `TransactionNotFoundException`.
- 500 — `InvalidTransactionDateException` (só com `status=completed` e data futura no body).

---

### `DELETE /api/v1/transactions/{id}`

Elimina a transação. Se estava `completed`, reverte o saldo. Regista `transaction_deleted`. **Permissão:** `destroyTransaction`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:**

```json
{
  "success": true,
  "message": "Transacção eliminada com sucesso!",
  "data": null,
  "errors": null
}
```

**Erros:**
- 422 — transação ligada a metas (`historical_linked` / `linked`).
- 403 — `UnauthorizedDeletedTransactionException`.
- 500 — `TransactionNotFoundException`.

---

### `POST /api/v1/transactions/{id}/confirm`

Confirma uma transação agendada: passa de `pending` a `completed` e ajusta o saldo. Não recebe body. Regista `transaction_confirmed`. **Permissão:** `confirmTransaction`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:** envelope com `message` `"Transacção confirmada com sucesso!"` e `data` no formato `TransactionResource`, com `status: "completed"`.

```json
{
  "success": true,
  "message": "Transacção confirmada com sucesso!",
  "data": {
    "id": 390,
    "accountId": 1,
    "type": "expense",
    "typeTransalated": "Despesa",
    "amount": 60,
    "date": "2026-11-18",
    "description": "Ginásio",
    "status": "completed",
    "statusTranslated": "Concluída",
    "categoryId": 20,
    "userId": 1
  },
  "errors": null
}
```

Nota: a API não verifica a data. Uma transação `pending` com data futura pode ser confirmada, embora a flag `actions.confirm` da listagem só fique `true` quando a data é até hoje.

**Erros:**
- 422 — transação ligada a metas (`historical_linked` / `linked`).
- 500 — `UnauthorizedConfirmTransactionException` (código 500, devia ser 403).
- 500 — `TransactionAlreadyConfirmedException` (já estava `completed`).
- 500 — `TransactionNotFoundException`.
