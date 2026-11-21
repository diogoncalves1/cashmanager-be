# API — Dívidas e pagamentos (`debts`, `debt-payments`)

Endpoints do módulo `Modules/Debts`: dívidas partilhadas e os respetivos pagamentos (cada pagamento cria uma despesa real numa conta). Convenções gerais (envelope, paginação, códigos de estado) em [README.md](README.md). Convites e membros (`debts/{id}/invite`, `accept`, `revoke`, `revoke-user`, `user-role`, `leave`, `*-invitations`) estão em [sharing.md](sharing.md). Contexto de negócio em [../architecture/modules/debts.md](../architecture/modules/debts.md).

Notas transversais deste módulo:

- Todos os endpoints exigem `auth:sanctum` + `setlocale`.
- Os controllers apanham `\Exception` e devolvem `fail($e->getMessage(), $e, $e->getCode())`: o código HTTP é o `$code` da exceção (ou 500). O campo `errors` traz a exceção serializada.
- Várias verificações fazem `$sharedRole->hasPermission(...)` **sem** testar `null`. Se o utilizador não for membro da dívida, ocorre um `Error` PHP (não é `\Exception`, não é apanhado) e a resposta é um 500 genérico do Laravel. Está assinalado em cada endpoint como "não membro → 500".
- As regras de negócio que deveriam ser 422 (`DebtNotPendingException`, `PaymentBeforeCurrentDateException`, …) usam código **500**.
- Formatação de montantes: `*Formated` usa `formatMoneyWithSymbolAndCurrency` (abreviado: `"1.50 k €"`); `*FormatedWithoutSymbol` e alguns outros usam `formatMoneyWithCurrency` (`"1,500.00 €"`). O símbolo vem antes do valor para as moedas em `config('currency.currenciesCodesPrefix')`.

---

## Dívidas

### `GET /api/v1/debts`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

`DebtPaginate` restringe o conjunto à pertença e `viewDebt` do utilizador autenticado. Filtros: status. Pesquisa: nome/descrição. Ordenação: id, name, total_amount, paid_amount, interest_rate, remaining_amount, status. Linhas preservam cálculos de juros, valores formatados, membros e actions. `meta.stats` permanece global.

---

### `GET /api/v1/debts/all`

Lista resumida das dívidas `pending` em que o utilizador tem a permissão `storeDebtPayment` (usada para escolher a dívida num formulário de pagamento). **Permissão:** `storeDebtPayment` (filtro, não erro).

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": [
    {
      "id": "3",
      "name": "Crédito automóvel",
      "currencySymbol": "€",
      "target": 12000,
      "targetFormated": "12.00 k €",
      "current": 3250.5,
      "currentFormated": "3.25 k €",
      "interestRate": 0.5,
      "monthlyAmount": 280
    }
  ],
  "errors": null
}
```

**Erros:** 500 em exceção inesperada.

### `GET /api/v1/debts/stats`

Resumo para o ecrã de pagamentos (`DebtRepository::getFormStats`). **Permissão:** membro (só conta dívidas onde o utilizador é membro).

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": {
    "transactionSummary": {
      "totalDebts": 3,
      "totalSaved": "6.25 k €",
      "currentYearTotalTransactions": 14,
      "thisMonth": "280.00 €",
      "lastMonth": "560.00 €",
      "difLastMonth": -150
    },
    "recentTransactions": [
      {
        "id": 41,
        "status": "completed",
        "statusTranslated": "Concluído",
        "amount": "280.00",
        "amountFormated": "280.00 €",
        "currencyCode": "EUR",
        "date": "2026-11-05",
        "description": "Prestação novembro",
        "userName": "Diogo",
        "debtName": "Crédito automóvel",
        "debtId": 3,
        "interestRate": "0.5000",
        "isMonthlyPayment": 1,
        "accountId": "2",
        "currencySymbol": "€",
        "currencyId": 1,
        "accountName": "Ordenado"
      }
    ]
  },
  "errors": null
}
```

- `totalSaved` = soma de `paid_amount` convertida para a moeda do utilizador.
- `thisMonth`/`lastMonth` = soma dos pagamentos `completed` no mês (todos os membros das dívidas do utilizador).
- `currentYearTotalTransactions` conta só os pagamentos feitos pelo próprio utilizador no ano corrente.
- `recentTransactions`: os 3 últimos pagamentos **do utilizador** (`DebtPaymentViewResource`).
- `difLastMonth` usa `Helpers::percentage(lastMonth, thisMonth − lastMonth)` = `((thisMonth − 2×lastMonth) / lastMonth) × 100`. A fórmula parece errada.

**Erros:** 500 em exceção inesperada.

### `POST /api/v1/debts`

Cria uma dívida e torna o utilizador membro com o role `creator`. Regista `debt_created` no activity log. **Permissão:** nenhuma.

**Body** (`DebtRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `name` | `required\|string\|max:255` | Nome |
| `total_amount` | `required\|numeric` | Montante em dívida (capital) |
| `currency_id` | `required\|exists:currencies,id` | Moeda |
| `start_date` | `required\|date` | Data de início |
| `due_date` | `required\|date` | Data limite |
| `interest_rate` | `nullable\|numeric` | Taxa de juro (%) por pagamento. A coluna não aceita `NULL`: enviar `null` explícito falha no insert (500); omitir usa o default 0 |
| `description` | `nullable\|string` | Descrição |
| `installments` | `nullable\|integrer` | ⚠️ A regra tem um erro de escrita (`integrer`). Enviar um valor não nulo provoca erro do validador (500). O campo não é usado: não enviar |
| `months` | sem validação | Número de prestações. Guardado tal e qual |
| `monthly_amount` | sem validação | Valor da prestação mensal. Guardado tal e qual |

```json
{
  "name": "Crédito automóvel",
  "total_amount": 12000,
  "currency_id": 1,
  "start_date": "2026-01-01",
  "due_date": "2029-12-31",
  "interest_rate": 0.5,
  "months": 48,
  "monthly_amount": 280,
  "description": "Stand Auto Lisboa"
}
```

**Resposta 200** (`DebtResource`):

```json
{
  "success": true,
  "message": "Dívida Crédito automóvel adicionada com sucesso!",
  "data": {
    "id": 3,
    "currencyId": 1,
    "name": "Crédito automóvel",
    "totalAmount": 12000,
    "paidAmount": null,
    "months": 48,
    "insterestRate": 0.5,
    "startDate": "2026-01-01",
    "dueDate": "2029-12-31",
    "description": "Stand Auto Lisboa",
    "isRecurring": null,
    "type": null,
    "status": null
  },
  "errors": null
}
```

- No `store`, o modelo não é recarregado: `paidAmount` e `status` vêm `null` (os defaults `0` e `pending` só existem na BD).
- `insterestRate` tem mesmo esta grafia. `isRecurring` e `type` não existem na tabela `debts` e vêm sempre `null`. `paidAt` só aparece quando não é nulo.

**Erros:**
- 422 — validação (formato Laravel).
- 500 — `installments` enviado; `interest_rate: null`; exceção inesperada.

### `GET /api/v1/debts/{id}`

Detalhe da dívida (`debts_view`), com membros e o total pago por cada um. **Permissão:** `viewDebt`.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | ID da dívida |

**Resposta 200** (`DebtViewResource`):

```json
{
  "success": true,
  "message": "",
  "data": {
    "id": 3,
    "name": "Crédito automóvel",
    "status": "pending",
    "statusTranslated": "Pendente",
    "totalAmount": 12000,
    "totalAmountFormated": "12.00 k €",
    "totalAmountFormatedWithoutSymbol": "12,000.00 €",
    "remainingAmount": "8,749.50 €",
    "paidAmount": 3250.5,
    "paidAmountFormatedWithoutSymbol": "3,250.50 €",
    "paidAmountFormated": "3.25 k €",
    "currencySymbol": "€",
    "currencyCode": "EUR",
    "currencyId": 1,
    "startDate": "2026-01-01",
    "dueDate": "2029-12-31",
    "userNames": "Diogo, Ana",
    "description": "Stand Auto Lisboa",
    "interestPaidFormated": "149.50 €",
    "interestPaid": "149.50",
    "missingAmountFormated": "8,749.50 €",
    "missingAmount": 8749.5,
    "percentageCompleted": -72.91,
    "totalPayments": "3400.00",
    "totalPaymentsFormated": "3,400.00 €",
    "users": [
      {
        "id": "1",
        "name": "Diogo",
        "paid": "2.98 k €",
        "sharedRole": { "id": "1", "name": "Criador", "code": "creator" }
      }
    ],
    "totalTransactions": 11,
    "months": 48,
    "monthsPaid": 10,
    "interestRate": 0.5,
    "paidAt": null,
    "monthlyAmount": 280,
    "monthlyAmountFormated": "280.00 €",
    "actions": {
      "edit": true,
      "destroy": true,
      "manage": true,
      "createTransactions": true,
      "markPaid": false
    }
  },
  "errors": null
}
```

- `remainingAmount` e `missingAmount*` são ambos `totalAmount − paidAmount` (o primeiro já formatado).
- `percentageCompleted` usa `Helpers::percentage(total, paid)` = `(paid − total) / total × 100`, ou seja, devolve o **negativo da percentagem em falta** (ex.: −72.91), não a percentagem paga.
- `users[].paid` (view `user_debt_payment_view`) = Σ dos pagamentos `completed` do membro de `amount − amount × interest_rate/100`.
- `actions`: `edit` = `editDebt`, `destroy` = `destroyDebt`, `manage` = `manageDebtUsers`, `createTransactions` = `storeDebtPayment`, `markPaid` = `editDebt` e `pending` e `paidAmount ≥ totalAmount`.

**Erros:**
- 403 — `UnauthorizedViewDebt` (sem `viewDebt`).
- 404 — `DebtNotFoundException`.
- 500 — não membro (`$sharedRole` nulo).

### `PUT /api/v1/debts/{id}`

Atualiza a dívida. Regista `debt_updated` com o diff por campo. **Permissão:** `editDebt`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Body:** igual a `POST /debts` (`DebtRequest`, todos os campos `required` voltam a ser exigidos). Campos guardados: `currency_id`, `name`, `total_amount`, `months`, `interest_rate`, `start_date`, `due_date`, `description`, `monthly_amount`. Não há bloqueio de moeda nem de montante (ao contrário das metas). `paid_amount` não é recalculado.

```json
{
  "name": "Crédito automóvel",
  "total_amount": 11500,
  "currency_id": 1,
  "start_date": "2026-01-01",
  "due_date": "2029-12-31",
  "interest_rate": 0.45,
  "months": 48,
  "monthly_amount": 270,
  "description": null
}
```

**Resposta 200:** `DebtResource` (ver `POST`), com `message` `"Dívida Crédito automóvel atualizada com sucesso!"`. Aqui `paidAmount` e `status` têm valor, porque o modelo veio da BD.

**Erros:**
- 422 — validação.
- 403 — `UnauthorizedUpdateDebt`.
- 404 — `DebtNotFoundException`.
- 500 — não membro; `installments` enviado.

### `DELETE /api/v1/debts/{id}`

Apaga a dívida (os pagamentos caem por `cascade`; as **transações** criadas pelos pagamentos não são apagadas por este endpoint) e apaga o activity log da dívida. **Permissão:** `destroyDebt`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:**

```json
{ "success": true, "message": "Dívida Crédito automóvel eliminada com sucesso!", "data": null, "errors": null }
```

**Erros:**
- 403 — `UnauthorizedDeleteDebt` (inclui não membro).
- 404 — `DebtNotFoundException`.

### `POST /api/v1/debts/{id}/mark-paid`

Marca a dívida como `paid` e define `paid_at`. Regista `debt_status_updated`. **Permissão:** `editDebt`.

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200:**

```json
{ "success": true, "message": "Dívida Crédito automóvel marcada como paga com sucesso!", "data": null, "errors": null }
```

**Erros:**
- 403 — `UnauthorizedUpdateDebt` (inclui não membro).
- 404 — `DebtNotFoundException`.
- 500 — `DebtNotPendingException` (já está `paid`); `DebtNotFullyPaidException` (`paid_amount < total_amount`).

### `POST /api/v1/debts/{id}/reset`

Volta a pôr a dívida em `pending` e limpa `paid_at`. Regista `debt_status_updated`. **Permissão:** `updateFinancialGoal` ⚠️ — o código verifica uma permissão de **metas** em vez de `editDebt` (bug conhecido, ver [known-issues](../architecture/known-issues.md)). Na prática só o `creator` (que tem todas as permissões) consegue fazer reset.

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200:**

```json
{ "success": true, "message": "Dívida Crédito automóvel reiniciada com sucesso!", "data": null, "errors": null }
```

**Erros:**
- 403 — `UnauthorizedUpdateDebt`.
- 404 — `DebtNotFoundException`.
- 500 — `DebtPendingException` (já está `pending`).

### `GET /api/v1/debts/{id}/activity`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

Autoriza primeiro o acesso ao recurso. `ActivityLogPaginate` restringe type=debt e subject_id=id. Query adicional: user_id (alias userId), search pelo nome do autor, order_by=id/created_at. Ordem por id descendente por omissão. Linhas apresentam user público, title/message traduzidos e createdAt; não expõem metadata interna.

---

## Pagamentos de dívida

Cada pagamento cria **sempre** uma despesa (`type = expense`, categoria `debtPayment`) na conta indicada, através de `TransactionRepository::store`. Por isso, além da permissão na dívida, o utilizador precisa de permissões na **conta**.

Cálculo ao concluir um pagamento: `juros = (total_amount − paid_amount) × interest_rate%`; `principal = max(0, amount − juros)`; `paid_amount += principal`; `months_paid++` se `is_monthly_payment`.

### `GET /api/v1/debt-payments`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

`DebtPaymentPaginate` restringe o conjunto à pertença e `viewDebtPayment` do utilizador autenticado. Filtros: status, debt_id, user_id, date[gte/lte]; aliases debtId, user/userId, dateFrom/dateTo. Pesquisa: nome da dívida/descrição. Ordenação: id, debt, amount, interest_paid, date, status. As ações usam permissões de pagamentos de dívida (incluindo confirmDebtPayment).

---

### `POST /api/v1/debt-payments`

Regista um pagamento (concluído ou agendado) e a despesa associada na conta. Regista `payment_added` (completed) ou `payment_scheduled` (pending) na dívida e `transaction_added`/`transaction_scheduled` na conta. **Permissão:** `storeDebtPayment` na dívida + `createTransaction` na conta.

**Body** (`DebtPaymentRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `debt_id` | `required\|exists:debts,id` | Dívida |
| `account_id` | `required\|exists:accounts,id` | Conta onde é criada a despesa. A moeda da conta não é comparada com a da dívida |
| `status` | `required\|in:completed,pending` | `completed`: data não pode ser futura. `pending`: data não pode ser passada |
| `amount` | `required\|numeric\|min:0` | Valor pago (capital + juros) |
| `date` | `required\|string` | Data `YYYY-MM-DD` (não é validada como data) |
| `interest_rate` | `nullable\|numeric\|min:0` | Taxa de juro (%) aplicada ao restante. `null` → 0. ⚠️ A chave tem de estar presente no body (mesmo a `null`), senão o acesso a `$input['interest_rate']` falha (500) |
| `description` | `nullable\|string` | Também usada como descrição da despesa |
| `is_monthly_payment` | sem validação | Booleano. Conta como prestação mensal (`months_paid`). ⚠️ Tem de ser enviado, senão o acesso a `$input['is_monthly_payment']` falha (500) |

```json
{
  "debt_id": 3,
  "account_id": 2,
  "status": "completed",
  "amount": 280,
  "date": "2026-11-05",
  "interest_rate": 0.5,
  "description": "Prestação novembro",
  "is_monthly_payment": true
}
```

**Resposta 200** (`DebtPaymentResource` = atributos crus do modelo `DebtPayment`, em snake_case):

```json
{
  "success": true,
  "message": "Pagamento da dívida adicionado com sucesso!",
  "data": {
    "debt_id": 3,
    "date": "2026-11-05",
    "status": "completed",
    "amount": 280,
    "description": "Prestação novembro",
    "interest_rate": 0.5,
    "is_monthly_payment": true,
    "user_id": 1,
    "transaction_id": 512,
    "updated_at": "2026-11-05T10:21:03.000000Z",
    "created_at": "2026-11-05T10:21:03.000000Z",
    "id": 41,
    "interest_paid": 43.7475,
    "debt": { "id": 3, "name": "Crédito automóvel", "total_amount": "12000.00", "paid_amount": 3486.75, "...": "..." }
  },
  "errors": null
}
```

- `interest_paid` e a relação `debt` (modelo `Debt` cru) só aparecem quando `status = completed` (são carregados pelo ajuste de `paid_amount`).

**Erros:**
- 422 — validação (formato Laravel).
- 403 — `UnauthorizedCreateDebtPayment` (sem `storeDebtPayment`); `UnauthorizedCreateTransactionException` (sem `createTransaction` na conta).
- 404 — `DebtNotFoundException`; `AccountNotFoundException`.
- 500 — `DebtNotInProgressException` (dívida não está `pending`); `PaymentBeforeCurrentDateException` (regra de datas); não membro da dívida; `interest_rate` ou `is_monthly_payment` ausentes.

### `GET /api/v1/debt-payments/{id}`

Detalhe de um pagamento (`debt_payments_view`). **Permissão:** `viewDebtPayment`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200** (`DebtPaymentViewResource`):

```json
{
  "success": true,
  "message": "",
  "data": {
    "id": 41,
    "status": "completed",
    "statusTranslated": "Concluído",
    "amount": "280.00",
    "amountFormated": "280.00 €",
    "currencyCode": "EUR",
    "date": "2026-11-05",
    "description": "Prestação novembro",
    "userName": "Diogo",
    "debtName": "Crédito automóvel",
    "debtId": 3,
    "interestRate": "0.5000",
    "isMonthlyPayment": 1,
    "accountId": "2",
    "currencySymbol": "€",
    "currencyId": 1,
    "accountName": "Ordenado"
  },
  "errors": null
}
```

**Erros:**
- 403 — `UnauthorizedViewDebtPaymentException` (inclui não membro).
- 404 — `DebtPaymentNotFoundException`.

### `PUT /api/v1/debt-payments/{id}`

Atualiza o pagamento e a despesa associada (`TransactionRepository::update`: `amount`, `date`, `description`). Se o pagamento estiver `completed`, ajusta o saldo da conta e recalcula `paid_amount` percorrendo todos os pagamentos da dívida por data. Regista `payment_updated` (dívida) e `transaction_updated` (conta). **Permissão:** `editDebtPayment` na dívida + `editTransaction` na conta da despesa.

**Parâmetros de rota:** `id` (int, obrigatório).

**Body** (`DebtPaymentUpdateRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `amount` | `required\|numeric\|min:0` | Novo valor |
| `date` | `required\|string` | Nova data. Num pagamento `completed` não pode ser futura |
| `interest_rate` | `required\|numeric\|min:0` | Taxa de juro (%) |
| `description` | `nullable\|string` | Descrição |
| `is_monthly_payment` | sem validação | Booleano. ⚠️ Num pagamento `completed` tem de ser enviado: é passado a um parâmetro `bool` e `null` provoca `TypeError` (500) |

Não é possível mudar `status`, `account_id` nem `debt_id`.

```json
{
  "amount": 300,
  "date": "2026-11-05",
  "interest_rate": 0.5,
  "description": "Prestação novembro (corrigida)",
  "is_monthly_payment": true
}
```

**Resposta 200:** `DebtPaymentResource` (atributos crus do `DebtPayment` após a atualização, mais a relação `debt` se for `completed`), `message` `"Pagamento da dívida atualizado com sucesso!"`.

**Erros:**
- 422 — validação.
- 403 — `UnauthorizedUpdateDebtPaymentException` (inclui não membro); `UnauthorizedUpdateTransactionException` (sem `editTransaction` na conta).
- 404 — `DebtPaymentNotFoundException`.
- 500 — `PaymentBeforeCurrentDateException` (pagamento `completed` com data futura); `TypeError` de `is_monthly_payment`.

### `DELETE /api/v1/debt-payments/{id}`

Apaga o pagamento e a despesa associada (sem verificar permissões na conta). Se estava `completed`, reverte o saldo da conta, recalcula `paid_amount` e decrementa `months_paid` quando `is_monthly_payment`. Regista `payment_deleted` e `transaction_deleted`. **Permissão:** `destroyDebtPayment`.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:**

```json
{ "success": true, "message": "Pagamento da dívida eliminado com sucesso!", "data": null, "errors": null }
```

**Erros:**
- 403 — `UnauthorizedDeleteDebtPaymentException` (inclui não membro).
- 404 — `DebtPaymentNotFoundException`.

### `POST /api/v1/debt-payments/{id}/confirm`

Confirma um pagamento agendado (`pending → completed`): confirma a despesa (ajusta o saldo da conta) e aplica juros/principal à dívida. Regista `payment_confirmed` e `transaction_confirmed`. **Permissão:** `confirmDebtPayment` na dívida + `confirmTransaction` na conta da despesa.

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200:**

```json
{ "success": true, "message": "Pagamento da dívida confirmado com sucesso!", "data": [], "errors": null }
```

⚠️ `DebtPaymentRepository::confirm` não devolve o resultado de `DB::transaction`, por isso o controller recebe `null` e `data` vem `[]`.

**Erros:**
- 403 — `UnauthorizedConfirmDebtPaymentException` (inclui não membro).
- 404 — `DebtPaymentNotFoundException`.
- 500 — `PaymentNotScheduledException` (não está `pending`); `PaymentBeforeCurrentDateException` (data futura); `UnauthorizedConfirmTransactionException` (sem `confirmTransaction` na conta; usa código 500); `TransactionAlreadyConfirmedException`.
