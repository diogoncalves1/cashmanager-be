# API — Metas financeiras e movimentos (`financial-goals`, `financial-goal-transactions`)

Endpoints do módulo `Modules/FinancialGoal`: metas de poupança partilhadas e o ledger de reservas (contribuições, libertações e utilizações). Convenções gerais (envelope, paginação, códigos de estado) em [README.md](README.md). Convites e membros (`financial-goals/{id}/invite`, `accept`, `revoke`, `revoke-user`, `user-role`, `leave`, `*-invitations`) estão em [sharing.md](sharing.md). Contexto de negócio em [../architecture/modules/financial-goal.md](../architecture/modules/financial-goal.md).

Notas transversais deste módulo:

- Todos os endpoints exigem `auth:sanctum` + `setlocale`.
- **Erros de validação dos FormRequests** (`FinancialGoalRequest`, `CreateFinancialGoalTransactionRequest`, `UpdateFinancialGoalTransactionRequest`) **não** usam o formato Laravel. Devolvem:
  ```json
  { "success": false, "message": "Validation Error", "errors": { "amount": ["The amount field is required."] } }
  ```
- **Erros de negócio do ledger** são `ValidationException` com chaves de `financialgoal::reservations.*`, devolvidas pelos controllers como 422:
  ```json
  { "success": false, "message": "O valor excede a reserva desta meta na conta escolhida.", "errors": { "amount": ["O valor excede a reserva desta meta na conta escolhida."] } }
  ```
  Nos erros abaixo escreve-se `422 campo: chave` (ex.: `422 amount: reserved`).
- **Permissões verificadas com `GoalReservations::permission`** usam `abort(403)`: resposta `403 {"success": false, "message": ""}` (sem mensagem). As exceções `Unauthorized*FinancialGoal` usam o envelope `fail()` com mensagem traduzida.
- `ModelNotFoundException` (`findOrFail`) → `404 {"success": false, "message": "Not found"}`.
- Formatação: `*Formated` usa `formatMoneyWithSymbolAndCurrency` (abreviado: `"1.50 k €"`); `*FormatedWithoutSymbol` e alguns outros usam `formatMoneyWithCurrency` (`"1,500.00 €"`).

---

## Metas

### `GET /api/v1/financial-goals`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

`FinancialGoalPaginate` restringe o conjunto à pertença e `viewFinancialGoal` do utilizador autenticado. Filtros: status e priority. Pesquisa: nome/descrição. Ordenação: id, name, total_amount, contributed_amount, priority, status. Preserva reservedAmount, usedAmount, fundedAmount, completedFunding, progresso e actions. `meta.stats` permanece global.

---

### `GET /api/v1/financial-goals/all`

Lista resumida das metas `in_progress` em que o utilizador tem `storeFinancialGoalTransaction` (para escolher a meta num formulário de movimento). **Permissão:** `storeFinancialGoalTransaction` (filtro, não erro).

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": [
    {
      "id": "7",
      "name": "Férias Açores",
      "currencyCode": "EUR",
      "currencySymbol": "€",
      "target": 1500,
      "targetFormated": "1.50 k €",
      "current": 600,
      "currentFormated": "600.00 €"
    }
  ],
  "errors": null
}
```

`current` é `contributed_amount` (contribuições − libertações), sem descontar utilizações.

### `GET /api/v1/financial-goals/stats`

Resumo para o ecrã de movimentos (`FinancialGoalRepository::getFormStats`). **Permissão:** membro.

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": {
    "transactionSummary": {
      "totalGoals": 3,
      "totalSaved": "1.12 k €",
      "currentYearTotalTransactions": 21,
      "thisMonth": "150.00 €",
      "lastMonth": "200.00 €",
      "difLastMonth": -125
    },
    "recentTransactions": [
      { "id": 88, "transactionId": null, "type": "contribution", "...": "ver GET /financial-goal-transactions/{id}" }
    ]
  },
  "errors": null
}
```

- `thisMonth`/`lastMonth` = contribuições − libertações `completed` do mês (utilizações contam 0), em todas as metas do utilizador.
- `currentYearTotalTransactions` conta só os movimentos criados pelo próprio utilizador.
- `recentTransactions`: os 3 últimos movimentos **do utilizador**, no formato `FinancialGoalTransactionViewResource` (ver `GET /financial-goal-transactions/{id}`).
- `difLastMonth` = `((thisMonth − 2×lastMonth) / lastMonth) × 100` (mesma fórmula suspeita de Debts).

### `POST /api/v1/financial-goals`

Cria a meta e torna o utilizador membro com o role `creator`. Regista `goal_created`. **Permissão:** nenhuma.

**Body** (`FinancialGoalRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `name` | `required\|string\|max:255` | Nome |
| `total_amount` | `required\|numeric\|min:0.01\|max:999999999999.99\|decimal:0,2` | Objetivo |
| `currency_id` | `required\|integer\|exists:currencies,id` | Moeda (as contas usadas têm de ter a mesma) |
| `start_date` | `required\|date` | Início |
| `due_date` | `required\|date\|after_or_equal:start_date` | Data limite |
| `description` | `nullable\|string` | Descrição |
| `priority` | `nullable\|string\|in:low,medium,high` | Prioridade |

```json
{
  "name": "Férias Açores",
  "total_amount": 1500,
  "currency_id": 1,
  "start_date": "2026-01-01",
  "due_date": "2027-06-30",
  "description": "Viagem de verão",
  "priority": "high"
}
```

**Resposta 200** (`FinancialGoalResource`):

```json
{
  "success": true,
  "message": "Meta financeira Férias Açores adicionada com sucesso!",
  "data": {
    "id": 7,
    "currencyId": 1,
    "name": "Férias Açores",
    "totalAmount": 1500,
    "contributedAmount": 0,
    "startDate": "2026-01-01",
    "dueDate": "2027-06-30",
    "status": null,
    "description": "Viagem de verão",
    "priority": "high",
    "createdAt": "2026-01-02T09:10:11.000000Z"
  },
  "errors": null
}
```

- No `store` o modelo não é recarregado: `status` vem `null` (o default `in_progress` só existe na BD).
- `completedAt` e `canceledAt` só aparecem quando não são nulos.

**Erros:**
- 422 — `Validation Error`.
- 500 — exceção inesperada.

### `GET /api/v1/financial-goals/{id}`

Detalhe da meta, com resumo do ledger, membros e contribuição de cada um. Junta em `meta` os 3 últimos movimentos e o resumo mensal. **Permissão:** `viewFinancialGoal`.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | ID da meta |

**Resposta 200** (`FinancialGoalViewResource` + `meta`):

```json
{
  "success": true,
  "message": "",
  "data": {
    "reservedAmount": 520,
    "usedAmount": 80,
    "fundedAmount": 600,
    "completedFunding": null,
    "id": 7,
    "name": "Férias Açores",
    "status": "in_progress",
    "statusTranslated": "Em Progresso",
    "priority": "high",
    "priorityTranslated": "Alta",
    "totalAmount": 1500,
    "totalAmountFormated": "1.50 k €",
    "totalAmountFormatedWithoutSymbol": "1,500.00 €",
    "contributedAmount": 600,
    "contributedAmountFormatedWithoutSymbol": "600.00 €",
    "contributedAmountFormated": "600.00 €",
    "currencySymbol": "€",
    "currencyCode": "EUR",
    "currencyId": "1",
    "startDate": "2026-01-01",
    "dueDate": "2027-06-30",
    "userNames": "Diogo, Ana",
    "description": "Viagem de verão",
    "missingAmountFormated": "900.00 €",
    "missingAmount": 900,
    "percentageCompleted": -60,
    "totalContributions": "650.00",
    "totalContributionsFormated": "650.00 €",
    "totalWithdrawals": "50.00",
    "totalWithdrawalsFormated": "50.00 €",
    "users": [
      {
        "id": "1",
        "name": "Diogo",
        "contribution": "450.00 €",
        "sharedRole": { "id": "1", "name": "Criador", "code": "creator" }
      }
    ],
    "totalTransactions": 9,
    "completedAt": null,
    "canceledAt": null,
    "updatedAt": "2026-11-01 18:00:00",
    "createdAt": "2026-01-02 09:10:11",
    "actions": { "edit": true, "destroy": true, "manage": true, "createTransactions": true }
  },
  "errors": null,
  "meta": {
    "transactions": [
      { "id": 88, "type": "contribution", "...": "FinancialGoalTransactionViewResource" }
    ],
    "monthlySummary": [
      { "monthYear": "2026-10", "balance": "450.00" },
      { "monthYear": "2026-11", "balance": "600.00" }
    ]
  }
}
```

- `reservedAmount`, `usedAmount`, `fundedAmount` (contribuições − libertações), `completedFunding` vêm de `GoalReservations::goalSummary`.
- `missingAmount` = `totalAmount − contributedAmount`. `percentageCompleted` tem o mesmo problema de sinal descrito na listagem.
- `users[].contribution` = Σ das contribuições `completed` desse membro (não desconta libertações).
- `actions`: `edit` = `updateFinancialGoal`, `destroy` = `destroyFinancialGoal`, `manage` = `manageFinancialGoalUsers`, `createTransactions` = `storeFinancialGoalTransaction`.
- `meta.transactions`: 3 últimos movimentos da meta (qualquer utilizador), formato `FinancialGoalTransactionViewResource`.
- `meta.monthlySummary`: saldo acumulado por mês de contribuições − libertações `completed` (utilizações contam 0).

**Erros:**
- 403 — `UnauthorizedViewFinancialGoal` (inclui não membro).
- 404 — `Not found` (meta inexistente).

### `PUT /api/v1/financial-goals/{id}`

Atualiza a meta. Regista `goal_updated` com o diff por campo. **Permissão:** `updateFinancialGoal`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Body:** igual a `POST /financial-goals` (`FinancialGoalRequest`, todos os obrigatórios voltam a ser exigidos).

```json
{
  "name": "Férias Açores 2027",
  "total_amount": 1800,
  "currency_id": 1,
  "start_date": "2026-01-01",
  "due_date": "2027-07-31",
  "description": null,
  "priority": "medium"
}
```

**Resposta 200:** `FinancialGoalResource` (ver `POST`), já com `status` e `contributedAmount` vindos da BD. `message`: `"Meta financeira Férias Açores 2027 atualizada com sucesso!"`.

**Erros:**
- 422 — `Validation Error`; `currency_id: currency_locked` (a meta já tem movimentos, de qualquer estado, e a moeda muda); `total_amount: target_locked` (meta `completed` e o objetivo muda).
- 403 — `UnauthorizedUpdateFinancialGoal`.
- 404 — `Not found`.

### `DELETE /api/v1/financial-goals/{id}`

Apaga a meta e o seu activity log. Só é possível sem histórico de movimentos. **Permissão:** `destroyFinancialGoal`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:**

```json
{ "success": true, "message": "Meta financeira Férias Açores excluída com sucesso!", "data": null, "errors": null }
```

**Erros:**
- 422 — `financial_goal_id: history` (existem movimentos).
- 403 — `UnauthorizedUpdateFinancialGoal` (o código lança a exceção de *update*, por isso a mensagem fala em "atualizar").
- 404 — `Not found`.

### `POST /api/v1/financial-goals/{id}/cancel`

`in_progress → canceled`, define `canceled_at`. Regista `goal_status_updated`. **Permissão:** `updateFinancialGoal`.

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200:** `FinancialGoalResource` com `"status": "canceled"` e `canceledAt`. `message`: `"Meta financeira Férias Açores cancelada com sucesso!"`.

**Erros:**
- 422 — `financial_goal_id: release_first` (há movimentos `pending` ou `reservedAmount > 0`).
- 403 — `UnauthorizedUpdateFinancialGoal`.
- 404 — `Not found`.
- 500 — `FinancialGoalNotInProgressException`.

### `POST /api/v1/financial-goals/{id}/complete`

`in_progress → completed`, define `completed_at` e guarda `completed_funding = contributed_amount`. Não cria despesas. Regista `goal_status_updated`. **Permissão:** `updateFinancialGoal`.

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200:** `FinancialGoalResource` com `"status": "completed"` e `completedAt`. `message`: `"Meta financeira Férias Açores concluída com sucesso!"`.

**Erros:**
- 422 — `financial_goal_id: pending` (há movimentos `pending`).
- 403 — `UnauthorizedUpdateFinancialGoal`.
- 404 — `Not found`.
- 500 — `FinancialGoalNotInProgressException`; `FinancialGoalNotFullyFundedException` (`contributed_amount < total_amount`).

### `POST /api/v1/financial-goals/{id}/reset`

`completed|canceled → in_progress`. Limpa `completed_at`, `canceled_at` e `completed_funding`. Regista `goal_status_updated`. **Permissão:** `updateFinancialGoal`.

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200:** `FinancialGoalResource` com `"status": "in_progress"`. `message`: `"Meta financeira Férias Açores voltou a estar em andamento!"`.

**Erros:**
- 403 — `UnauthorizedUpdateFinancialGoal`.
- 404 — `Not found`.
- 500 — `FinancialGoalInProgressException` (já está `in_progress`).

### `GET /api/v1/financial-goals/{id}/activity`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

Autoriza primeiro o acesso ao recurso. `ActivityLogPaginate` restringe type=financial_goal e subject_id=id. Query adicional: user_id (alias userId), search pelo nome do autor, order_by=id/created_at. Ordem por id descendente por omissão. Linhas apresentam user público, title/message traduzidos e createdAt; não expõem metadata interna.

---

### `GET /api/v1/financial-goals/{id}/reservations`

Contexto do ledger para o formulário de movimentos: resumo da meta e contas elegíveis. **Permissão:** `viewFinancialGoal`.

Contas elegíveis: contas de que o utilizador é membro, **com a mesma moeda da meta**, onde tem `viewAccount` e `createTransaction`. As contas inativas também aparecem (`active: false`): servem para libertações.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200** (sem `message`/`errors`; só `success` e `data`):

```json
{
  "success": true,
  "data": {
    "reservedAmount": 520,
    "usedAmount": 80,
    "fundedAmount": 600,
    "completedFunding": null,
    "accounts": [
      {
        "id": "2",
        "name": "Ordenado",
        "active": true,
        "currencyCode": "EUR",
        "reservedForGoal": 300,
        "balance": 2150.4,
        "reservedAmount": 420,
        "availableAmount": 1730.4,
        "reservationShortfall": 0
      }
    ]
  }
}
```

- `reservedForGoal`: reserva líquida desta meta nessa conta (contribuições − libertações − utilizações `completed`). É o máximo para uma libertação ou utilização.
- `reservedAmount`/`availableAmount`: reservas de **todas** as metas na conta e `balance − reservedAmount`. `reservationShortfall` = `max(0, −availableAmount)`.

**Erros:** 403 `{"success": false, "message": ""}`; 404 `Not found`. Outras exceções não são apanhadas (500 do Laravel).

### `GET /api/v1/financial-goals/{id}/eligible-expenses`

Despesas de uma conta que podem ser ligadas a uma utilização (`type = usage` com `transaction_id`). Paginação fixa de 20. **Permissão:** `storeFinancialGoalTransaction` na meta + `viewAccount`, `viewTransaction` e `createTransaction` na conta.

Critérios: `type = expense`, `status = completed`, `date ≤ hoje`, não ligadas a movimentos históricos de meta que não sejam `usage`. Despesas já totalmente alocadas continuam a aparecer com `unallocatedAmount: 0`.

**Parâmetros de rota/query:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int (rota) | sim | ID da meta |
| `account_id` | int | sim | Conta (mesma moeda da meta) |
| `page` | int ≥ 1 | não | Página (default 1) |
| `search` | string ≤ 100 | não | Pesquisa `LIKE` na `description` |

**Resposta 200:**

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": "512",
        "date": "2026-10-28",
        "description": "Bilhetes de avião",
        "amount": 230,
        "unallocatedAmount": 150
      }
    ],
    "page": 1,
    "hasMore": false
  }
}
```

`unallocatedAmount` = `amount − Σ utilizações já ligadas a esta despesa` (mínimo 0).

**Erros:**
- 422 — validação de query (`{"success": false, "message", "errors"}`); `account_id: currency` (moeda diferente).
- 403 — `{"success": false, "message": ""}`.
- 404 — `Not found` (meta ou conta).

---

## Movimentos de metas (ledger de reservas)

Tipos de movimento (`type`):

| Tipo | Efeito | Estados permitidos |
|---|---|---|
| `contribution` | Reserva dinheiro da conta para a meta. **Não** altera o saldo da conta nem cria transação | `completed` ou `pending` |
| `withdrawal` | Liberta reserva. Não pode exceder a reserva da meta nessa conta | `completed` ou `pending` |
| `usage` | Gasta dinheiro reservado numa despesa real: liga uma despesa existente (`transaction_id`) ou cria uma (`category_id`). Não pode exceder a reserva | só `completed` |

`contributed_amount` da meta é recalculado (`sync`) após cada criação/alteração: Σ contribuições − Σ libertações `completed`.

### `GET /api/v1/financial-goal-transactions`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

`FinancialGoalTransactionPaginate` restringe o conjunto à pertença e `viewFinancialGoalTransaction` do utilizador autenticado. Filtros: type, status, user_id, financial_goal_id, date[gte/lte]; aliases userId, financialGoalId, dateFrom/dateTo. Pesquisa: descrição. Ordenação: id, date, amount, type, status. A pertença usa EXISTS sem join à view agregada das metas. Linhas preservam saldos e restrições das ações.

---

### `POST /api/v1/financial-goal-transactions`

Cria um movimento (contribuição, libertação ou utilização). Regista `financial_transaction_added` (completed) ou `financial_transaction_scheduled` (pending). **Permissão:** `storeFinancialGoalTransaction` na meta + `createTransaction` na conta. Para `usage` com `transaction_id`, também `viewAccount` e `viewTransaction` na conta.

**Body** (`CreateFinancialGoalTransactionRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `financial_goal_id` | `required\|integer\|exists:financial_goals,id` | Meta |
| `account_id` | `required\|integer\|exists:accounts,id` | Conta da reserva. Mesma moeda da meta; ativa (exceto `withdrawal`) |
| `type` | `required\|string\|in:withdrawal,contribution,usage` | Tipo de movimento |
| `status` | `required\|string\|in:completed,pending` | `usage` só aceita `completed` |
| `amount` | `required\|numeric\|min:0.01\|max:999999999999.99\|decimal:0,2` | Valor |
| `date` | `required\|date` | `completed`: não pode ser futura. `pending`: não pode ser passada. Em `usage` com `transaction_id` é substituída pela data da despesa |
| `description` | `nullable\|string\|max:255` | Descrição (também da despesa criada em `usage`) |
| `transaction_id` | `nullable\|integer\|exists:transactions,id` | Só para `usage`: despesa existente a ligar. Ignorado nos outros tipos |
| `category_id` | `exclude_unless:type,usage\|nullable\|integer\|exists:categories,id` | Só para `usage` sem `transaction_id`: categoria de despesa (selecionável pelo utilizador) da despesa a criar |
| `request_key` | `required\|string\|max:100` | Chave de idempotência (ver abaixo) |
| `recurring_transaction_id` | `prohibited` | Reservado ao gerador de recorrentes |
| `is_recurring` | `prohibited` | Reservado ao gerador de recorrentes |

**Idempotência (`request_key`):** o cliente gera uma chave única por submissão (ex.: UUID) e reenvia a mesma chave em retries. O servidor guarda em `financial_goal_request_receipts` a chave e um hash SHA-256 do pedido (`financial_goal_id`, `account_id`, `amount`, `date`, `type`, `status`, `description`, `request_key`, `transaction_id`, `category_id`).
- Mesma chave + mesma meta + mesmo utilizador + mesmo hash → devolve o movimento já criado (200, sem duplicar).
- Mesma chave com dados diferentes (ou outra meta/utilizador) → `422 request_key: retry`.
- O hash usa os valores tal como chegam no JSON: `150` e `"150"` dão hashes diferentes.

**Exemplos de body:**

Contribuição (reserva):

```json
{
  "financial_goal_id": 7,
  "account_id": 2,
  "type": "contribution",
  "status": "completed",
  "amount": 150,
  "date": "2026-11-05",
  "description": "Novembro",
  "request_key": "3f1c2a9e-6d0b-4c7e-9a51-2b7e8f0d4c11"
}
```

Libertação agendada:

```json
{
  "financial_goal_id": 7,
  "account_id": 2,
  "type": "withdrawal",
  "status": "pending",
  "amount": 50,
  "date": "2026-12-01",
  "description": "Libertar para Natal",
  "request_key": "8a7d5e31-0f4b-4b62-8c0e-1d2f3a4b5c6d"
}
```

Utilização ligada a uma despesa existente (`date` é substituída pela da despesa):

```json
{
  "financial_goal_id": 7,
  "account_id": 2,
  "type": "usage",
  "status": "completed",
  "amount": 80,
  "date": "2026-10-28",
  "transaction_id": 512,
  "request_key": "c2b1a0f9-1e2d-4c3b-8a7f-6e5d4c3b2a10"
}
```

Utilização que cria a despesa (cria uma transação `expense` na conta, altera o saldo, `expense_created = true`):

```json
{
  "financial_goal_id": 7,
  "account_id": 2,
  "type": "usage",
  "status": "completed",
  "amount": 120,
  "date": "2026-11-10",
  "description": "Alojamento Ponta Delgada",
  "category_id": 14,
  "request_key": "5d4c3b2a-1f0e-4d9c-8b7a-6f5e4d3c2b1a"
}
```

**Resposta 200** (`FinancialGoalTransactionResource`):

```json
{
  "success": true,
  "message": "Transação da meta financeira adicionada à meta Férias Açores com sucesso!",
  "data": {
    "id": 88,
    "financialGoalId": 7,
    "userId": 1,
    "transactionId": null,
    "type": "contribution",
    "amount": 150,
    "date": "2026-11-05",
    "description": "Novembro",
    "status": "completed"
  },
  "errors": null
}
```

`transactionId` vem preenchido nas utilizações (despesa ligada ou criada).

**Erros** (verificados por esta ordem):
- 422 — `Validation Error` (FormRequest).
- 404 — `Not found` (conta ou meta).
- 403 — `{"success": false, "message": ""}`: sem `storeFinancialGoalTransaction` na meta, sem `createTransaction` na conta, ou (usage com despesa) sem `viewAccount`/`viewTransaction`.
- 422 `account_id: currency` — moeda da conta ≠ moeda da meta.
- 422 `account_id: inactive` — conta inativa e o tipo não é `withdrawal`.
- 422 `request_key: retry` — chave reutilizada com outro pedido.
- 422 `financial_goal_id: closed` — meta `canceled` e o tipo não é `withdrawal`; meta `completed` e o tipo é `contribution`; meta fora de `in_progress` e `status` ≠ `completed`.
- 422 `date: date` — `completed` com data futura ou `pending` com data passada.
- 422 `status: usage_completed` — `usage` com `status = pending`.
- 422 `amount: amount` — valor ≤ 0.
- 422 `amount: reserved` — `withdrawal`/`usage` `completed` acima da reserva da meta nessa conta.
- 422 `transaction_id: expense` — a despesa não é da conta, não é `expense`, não está `completed`, tem data futura, ou já está ligada a um movimento histórico que não é `usage`.
- 422 `amount: allocated` — a soma das utilizações excede o valor da despesa.
- 422 `category_id: category` — `usage` sem `transaction_id` nem `category_id`. A categoria também é validada por `SelectableCategory` (422 se não for selecionável).
- 500 — exceção inesperada (o `store` força sempre 500).

### `GET /api/v1/financial-goal-transactions/{id}`

Detalhe de um movimento. **Permissão:** `viewFinancialGoalTransaction`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200** (`FinancialGoalTransactionViewResource`):

```json
{
  "success": true,
  "message": "",
  "data": {
    "id": 88,
    "transactionId": null,
    "type": "contribution",
    "typeTranslated": "Reserva",
    "status": "completed",
    "statusTranslated": "Completa",
    "amount": 150,
    "amountFormated": "150.00 €",
    "date": "2026-11-05",
    "description": "Novembro",
    "userName": "Diogo",
    "financialGoalName": "Férias Açores",
    "financialGoalId": 7,
    "currencySymbol": "€",
    "currencyCode": "EUR",
    "currencyId": 1,
    "accountId": 2,
    "accountName": "Ordenado",
    "accountType": "Conta Bancária",
    "sharedRole": "Criador",
    "balanceAfter": "520.00 €",
    "balanceBefore": "370.00 €",
    "actions": { "edit": false, "destroy": false, "confirm": false, "store": true }
  },
  "errors": null
}
```

- `sharedRole`: nome (no idioma do utilizador) do role que o **autor** do movimento tem na meta.
- `actions`: `edit` = `updateFinancialGoalTransaction` e `pending`; `destroy` = `destroyFinancialGoalTransaction` e (`pending` ou `usage`); `confirm` = `confirmScheduledFinancialGoalTransactions` e `pending` e `date ≤ hoje`; `store` = `storeFinancialGoalTransaction`.

**Erros:** 403 `{"success": false, "message": ""}`; 404 `Not found`.

### `PUT /api/v1/financial-goal-transactions/{id}`

Edita um movimento **agendado** (`pending`). Os movimentos `completed` são imutáveis: as correções fazem-se com uma reserva ou libertação explícita. Não regista atividade. **Permissão:** `updateFinancialGoalTransaction` na meta + `createTransaction` na conta do movimento.

**Parâmetros de rota:** `id` (int, obrigatório).

**Body** (`UpdateFinancialGoalTransactionRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `amount` | `required\|numeric\|min:0.01\|max:999999999999.99\|decimal:0,2` | Novo valor |
| `date` | `required\|date` | Nova data (não pode ser passada) |
| `description` | `nullable\|string\|max:255` | Descrição |

Não é possível mudar `type`, `status`, conta nem meta.

```json
{ "amount": 60, "date": "2026-12-01", "description": "Libertar para Natal" }
```

**Resposta 200:** `FinancialGoalTransactionResource` (ver `POST`). `message`: `"Transação da meta financeira atualizada na meta Férias Açores com sucesso!"`.

**Erros:**
- 422 — `Validation Error`; `amount: immutable` (movimento `completed`); `date: date` (meta fora de `in_progress` ou data passada).
- 403 — `{"success": false, "message": ""}`.
- 404 — `Not found`.

### `DELETE /api/v1/financial-goal-transactions/{id}`

Apaga um movimento `pending` (qualquer tipo) ou desliga uma utilização `completed` (a despesa real mantém-se; se o dinheiro já não estiver disponível aparece como `reservationShortfall`). Não regista atividade. **Permissão:** `destroyFinancialGoalTransaction` na meta + `createTransaction` na conta.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:**

```json
{ "success": true, "message": "Transação da meta financeira excluída da meta Férias Açores com sucesso!", "data": null, "errors": null }
```

**Erros:**
- 422 — `amount: immutable` (contribuição ou libertação `completed`).
- 403 — `{"success": false, "message": ""}`.
- 404 — `Not found`.

### `POST /api/v1/financial-goal-transactions/{id}/confirm`

Confirma um movimento agendado (`pending → completed`). É idempotente: se já estiver `completed`, devolve o movimento sem o aplicar de novo. Não regista atividade. **Permissão:** `confirmScheduledFinancialGoalTransactions` na meta + `createTransaction` na conta.

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200:** `FinancialGoalTransactionResource` com `"status": "completed"`. `message`: `"Transação da meta financeira confirmada na meta Férias Açores com sucesso!"`.

**Erros:**
- 422 — `date: date` (data futura); `financial_goal_id: closed` (meta fora de `in_progress`, conta inativa num tipo que não é `withdrawal`, ou moeda diferente); `amount: reserved` (libertação acima da reserva atual).
- 403 — `{"success": false, "message": ""}`.
- 404 — `Not found`.

## Eliminação assistida e libertação das reservas (2026-11-21)

- `GET /api/v1/financial-goals/{id}/deletion-context` requer `destroyFinancialGoal` e devolve `success: true`, `data`: resumo (`reservedAmount`, `usedAmount`, `fundedAmount`, `completedFunding`), `currencyCode`, `status`, `hasHistory`, `pendingCount`, `canDelete`, `canReleaseAndCancel` e `accounts`.
- Cada conta com reserva positiva tem `{id, name, reservedAmount, canRelease}`. Sem `viewAccount`, id/name são null e canRelease é false. Os totais da meta continuam visíveis ao utilizador autorizado; não se revelam identidades de contas privadas.
- O FE consulta o contexto ao abrir “Apagar”. Sem histórico, apresenta a confirmação de DELETE. Com histórico, explica a preservação dos movimentos e oferece abrir a meta para gerir utilizações/pendentes ou “Libertar reservas e cancelar meta”. Pendentes bloqueiam a ação automática; erros mantêm o diálogo aberto e atualizam o contexto.
- `POST /api/v1/financial-goals/{id}/release-and-cancel` não recebe montantes/contas do cliente. Recalcula o reservado por conta, cria um withdrawal completed pelo remanescente de cada origem e marca a meta canceled. Sucesso: `{success: true, data: {status: "canceled"}}`.
- Exige `destroyFinancialGoal` e `updateFinancialGoal`; para cada reserva positiva exige `storeFinancialGoalTransaction` na meta e `viewAccount`/`createTransaction` na conta. Pode libertar reservas em contas inativas. A conta tem de ter a moeda da meta.
- A operação é atómica (user → contas ordenadas → meta → movimentos). Leituras com lock veem o estado atual; alteração das contas de origem durante a aquisição dos locks devolve 422 para atualizar/repetir. Pendentes, origem desconhecida e reservas negativas impedem a operação. Falta de permissão devolve 403; nada é parcialmente libertado.
- Repetir o pedido não duplica levantamentos. Não altera o saldo total das contas: aumenta apenas o disponível. Compras, utilizações, movimentos anteriores e completedFunding são preservados. Mesmo sem reserva restante, uma meta com histórico pode ser cancelada pelo mesmo fluxo; não é apagada definitivamente. DELETE continua a recusar metas com histórico (422).
- Não há migration nova. Publicar BE antes do FE. O endpoint é executado apenas após escolha explícita no diálogo, nunca ao abrir o aviso.
