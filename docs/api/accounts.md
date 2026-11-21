# API — Contas

Endpoints de contas (`Modules/Accounts`): CRUD, picker, estado, histórico de atividade e o panorama global `account/resume`. Convenções gerais (envelope, paginação, códigos de estado) em [README.md](README.md). Convites e membros das contas estão em [sharing.md](sharing.md).

Todos os endpoints exigem `auth:sanctum` e passam pelo middleware `setlocale`.

Notas comuns a este ficheiro:

- Os controllers apanham qualquer `\Exception` e devolvem `fail($e->getMessage(), $e, $e->getCode())`. Uma exceção com código `0` (por exemplo `ModelNotFoundException` ou `ValidationException` lançada dentro do repository) acaba em **500**, e não em 404/422.
- Montantes `*Formated` usam `Helpers::formatMoneyWithSymbolAndCurrency` (abrevia com `k`/`M` a partir de 1000, ex.: `"1.25 k €"`) ou `formatMoneyWithCurrency` (valor completo, ex.: `"1,250.00 €"`). O símbolo vai antes ou depois conforme `config('currency.currenciesCodesPrefix')`.

---

### `GET /api/v1/accounts`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

`AccountPaginate` consulta Account, com pertença e `viewAccount` na mesma membership. Carrega moeda, membros e count de transações; não usa accounts_view.

| Query | Efeito |
|---|---|
| type[eq], active[eq], currency_id[eq] | Tipo, estado 1/0 e moeda |
| search | Nome da conta |
| order_by | id, name, type, balance |

As linhas usam `Accounts/Lists/AccountListResource`: active booleano, moeda aninhada em currency e código/símbolo de topo, saldo numérico/formatado, reservas/disponível e totalTransactions. `meta.stats` continua global e converte totais monetários de contas com updateUserBalance para a moeda preferida.

---

### `POST /api/v1/accounts`

Cria uma conta com saldo 0 e associa o utilizador com o shared role `creator`. Regista `account_created` no activity log. **Permissão:** nenhuma.

**Body** (`AccountRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `name` | `required\|string\|max:255` | Nome da conta |
| `currency_id` | `required\|exists:currencies,id` | Moeda da conta |
| `type` | `required\|string\|in:cash,bank_account,credit_card,digital_wallet` | Tipo |
| `active` | `required` | Estado inicial. A regra só exige presença; enviar `true`/`false` (ou `1`/`0`) |

O saldo não é aceite (começa sempre a 0). A resposta usa `Accounts/AccountResource`: a moeda é aninhada em `currency` apenas quando a relação foi carregada. O fluxo de criação atual não a carrega, pelo que esse campo é omitido; não devolve `currencyId`.

```json
{
  "name": "Ordenado",
  "currency_id": 1,
  "type": "bank_account",
  "active": true
}
```

**Resposta 200:**

```json
{
  "success": true,
  "message": "Conta Ordenado adicionada com sucesso!",
  "data": {
    "id": 7,
    "name": "Ordenado",
    "type": "bank_account",
    "balance": 0,
    "active": true
  },
  "errors": null
}
```

**Erros:**
- 422 — validação.
- 500 — erro inesperado.

---

### `GET /api/v1/accounts/all`

Picker sem paginação, via `getAccountsByUserPermission($user, 'createTransaction')`. Filtra contas ativas do utilizador e carrega `currency`.

```json
{
  "success": true,
  "message": "",
  "data": [
    {
      "id": "1",
      "name": "Ordenado",
      "currency": { "id": "1", "name": "Euro", "symbol": "€", "rate": 1.1, "code": "EUR" },
      "type": "bank_account"
    }
  ],
  "errors": null
}
```

Resource atual: `Accounts/AccountBasicResource`. Já não inclui `balance`, `reservedAmount`, `availableAmount`, `reservationShortfall` ou `typeOrg`; `type` é o código original.

**Limitação de autorização em revisão:** a query de permissão percorre `accountUsers.sharedRole.permissions` sem restringir esse `accountUsers` ao membro autenticado. Assim, comprova que existe um membro com a permissão na conta, além de a conta pertencer ao utilizador. Não equivale a comprovar que é o seu próprio role. Ver [problemas conhecidos](../architecture/known-issues.md).

**Erros:** 500 em falhas inesperadas.

---

### `GET /api/v1/accounts/{id}`

Detalhe da conta, com resumo mensal, categorias de despesa, comparação com o mês anterior e gráficos de saldo em `meta`. **Autorização atual:** `Gate::authorize('show', $account)`, cuja policy aceita membership; não verifica `viewAccount`. O resumo global mantém a sua verificação própria de `viewAccount`.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | Id da conta |

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": {
    "balance": 1250.5,
    "reservedAmount": 200,
    "availableAmount": 1050.5,
    "reservationShortfall": 0,
    "id": 1,
    "name": "Ordenado",
    "currencySymbol": "€",
    "currencyCode": "EUR",
    "currencyId": "1",
    "type": "bank_account",
    "typeTranslated": "Conta Bancária",
    "balanceFormated": "1.25 k €",
    "balanceFormatedWithoutSymbol": "1,250.50 €",
    "active": true,
    "statusTranslated": "Ativo",
    "totalTransactions": 42,
    "totalRevenues": "3,200.00 €",
    "totalExpenses": "1,949.50 €",
    "createdAt": "2026-01-10 18:22:01",
    "users": [
      {
        "id": "1",
        "name": "Diogo",
        "username": "diogo",
        "sharedRole": { "id": "1", "name": "Criador", "code": "creator" }
      }
    ],
    "invites": [
      {
        "id": "5",
        "name": "Ana",
        "username": "ana",
        "sharedRole": { "id": "3", "name": "Membro", "code": "member" }
      }
    ],
    "actions": { "edit": true, "destroy": true, "manage": true, "createTransactions": true }
  },
  "errors": null,
  "meta": {
    "monthlyResume": [
      {
        "month": "2026-11",
        "totalRevenue": 1500,
        "totalRevenueFormated": "1.50 k €",
        "totalRevenueFormatedWithoutSymbol": "1,500.00 €",
        "totalExpense": 820.3,
        "totalExpenseFormated": "820.30 €",
        "totalExpenseFormatedWithoutSymbol": "820.30 €",
        "profit": "679.70 €",
        "profitFormated": "679.70 €"
      }
    ],
    "extraData": {
      "monthlyRevenuesAmount": 1500,
      "monthlyExpensesAmount": 820.3,
      "monthlyRevenues": "1,500.00 €",
      "monthlyExpenses": "820.30 €",
      "revenuesVsLastMonth": 7.14,
      "expensesVsLastMonth": -12.5,
      "balanceVsLastMonth": 4.2,
      "balanceLastMonth": 1200,
      "lastMonthRevenue": "1,400.00 €",
      "lastMonthExpenses": "937.50 €"
    },
    "balanceChart": {
      "charts": {
        "weekly": [
          { "date": "2026-11-15", "amount": 1250.5, "transactionAmount": -35.2 }
        ],
        "monthly": [],
        "quarterly": [],
        "annualy": []
      }
    },
    "categorySummary": {
      "data": [
        {
          "category": "Supermercado",
          "value": 420.1,
          "color": "#22c55e",
          "valueFormated": "420.10 €",
          "valueFormatedWithoutSymbol": "420.10 €",
          "icon": "ShoppingCart",
          "count": 12
        }
      ],
      "total": 1949.5,
      "totalFormated": "1,949.50 €"
    }
  }
}
```

- `data.balance`, `reservedAmount`, `availableAmount`, `reservationShortfall` vêm de `GoalReservations::accountSummary`. `totalRevenues`/`totalExpenses` são strings formatadas (sempre positivas).
- `users`/`invites`: membros e convites `pending`, com o respetivo `sharedRole` (o role `member` do exemplo é ilustrativo; só o `creator` é semeado por migration). `actions` reflete as permissões do utilizador na conta.
- `monthlyResume`: totais `completed` por mês (`YYYY-MM`), do mais recente para o mais antigo.
- `extraData`: mês atual vs. mês anterior, só transações `completed`. As percentagens são `(atual − anterior) / anterior × 100` (0 quando o anterior é 0).
- `balanceChart.charts`: saldo acumulado por dia (`amount`) e movimento do dia (`transactionAmount`) para os últimos 7 (`weekly`), 30 (`monthly`), 90 (`quarterly`) e 365 dias (`annualy`). Só há pontos nos dias com transações `completed`.
- Dúvida/bug: as séries de `balanceChart` são filtradas com `where()` sem `values()`, por isso mantêm as chaves originais. Quando a primeira chave não é 0, o JSON sai como objeto (`{"12": {...}}`) em vez de array. Pela mesma razão, `extraData.balanceLastMonth` lê `charts.monthly[0]`, que só existe se a primeira transação da conta tiver menos de 30 dias; caso contrário fica 0.
- `categorySummary`: despesas agrupadas por categoria (nome traduzido para `user_preferences.lang`). `value` só soma `completed`, mas `count` conta todas as despesas, incluindo `pending`.

**Erros:**
- Não membro: `Gate::authorize` lança `AuthorizationException`; o catch atual converte código 0 em 500, apesar de a recusa de autorização pretender 403.
- 404 — `AccountNotFoundException`.
- 500 — erro inesperado.

---

### `PUT|PATCH /api/v1/accounts/{id}`

Atualiza nome, tipo, moeda e estado. Regista `account_updated` com as alterações no activity log. **Autorização atual:** policy `update` (creator ou `editAccount`); recusas lançadas pelo Gate podem ser convertidas em 500 pelo catch genérico.

**Parâmetros de rota:** `id` (int, obrigatório) — id da conta.

**Body:** o mesmo conjunto de campos do `POST`, mas com presença `sometimes` em `PUT` e `PATCH`: aceita atualização parcial. Os campos enviados continuam a ser validados.

```json
{
  "name": "Ordenado (CGD)",
  "currency_id": 1,
  "type": "bank_account",
  "active": true
}
```

**Resposta 200:** igual ao `POST`, com `message` `"Conta Ordenado (CGD) atualizada com sucesso!"` e `data` no formato `Accounts/AccountResource` (`id`, `name`, `type`, `balance`, `active` e `currency` apenas quando a relação é carregada).

**Erros:**
- 422 — validação do `AccountRequest`.
- 403 — `UnauthorizedUpdateAccountException`.
- 500 — conta inexistente (`firstOrFail`, devolve 500 e não 404).
- 500 — mudar `currency_id` numa conta com movimentos de metas (`GoalReservations::invalid('currency_id', 'currency_locked')`). É uma `ValidationException`, mas o controller apanha-a e devolve 500 com a mensagem "Não podes alterar a moeda depois de registar movimentos."

---

### `DELETE /api/v1/accounts/{id}`

Elimina a conta e o seu activity log. **Permissão:** Gate delete da AccountPolicy, limitado ao creator. As flags da lista continuam a anunciar destroyAccount e não substituem a autorização da policy.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:**

```json
{
  "success": true,
  "message": "Conta Ordenado eliminada com sucesso!",
  "data": null,
  "errors": null
}
```

**Erros:**
- 403 — `UnauthorizedDeletedAccountException`.
- 500 — conta inexistente (`firstOrFail`).
- 500 — conta com histórico de reservas de metas (`GoalReservations::invalid('account_id', 'account_history')`). Também devia ser 422.

---

### `PATCH /api/v1/accounts/{id}/status`

Alterna `active` (ativa ↔ inativa). Não recebe body. Regista `account_status_updated`. **Autorização atual:** policy `update` (creator ou `editAccount`); recusas lançadas pelo Gate podem ser convertidas em 500 pelo catch genérico.

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:**

```json
{
  "success": true,
  "message": "Conta Ordenado desativada com sucesso!",
  "data": {
    "id": 1,
    "name": "Ordenado",
    "type": "bank_account",
    "balance": 1250.5,
    "active": false
  },
  "errors": null
}
```

**Erros:**
- 403 — `UnauthorizedUpdateAccountException`.
- 500 — conta inexistente (`firstOrFail`).

---

### `GET /api/v1/accounts/{id}/activity`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

Autoriza primeiro o acesso ao recurso. `ActivityLogPaginate` restringe type=account e subject_id=id. Query adicional: user_id (alias userId), search pelo nome do autor, order_by=id/created_at. Ordem por id descendente por omissão. Linhas apresentam user público, title/message traduzidos e createdAt; não expõem metadata interna.

---

### `GET /api/v1/account/resume`

Panorama global das contas do utilizador numa **única moeda** (não converte). Considera as contas onde o role tem `viewAccount` e que usam a moeda escolhida. Exclui transações com data futura. **Permissão:** `viewAccount` (filtro das contas).

**Parâmetros de query:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `period` | string | não | `3m` (mês atual + 2 anteriores), `12m` (por defeito, mês atual + 11), `ytd` (desde 1 de janeiro), `all` (desde o mês da primeira transação) |
| `currency` | string | não | Código ISO de 3 letras maiúsculas (`regex:/^[A-Z]{3}$/`). Tem de ser a moeda de pelo menos uma das contas. Por defeito: a moeda preferida do utilizador, se existir nas contas; senão, a primeira por ordem alfabética |

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": {
    "generatedAt": "2026-11-18T09:30:00+00:00",
    "currencyCode": "EUR",
    "currencies": ["EUR", "USD"],
    "period": { "key": "3m", "from": "2026-09-01", "to": "2026-11-18" },
    "summary": {
      "totalAccounts": 2,
      "activeAccounts": 2,
      "balance": 3450.75,
      "income": 4500,
      "expenses": 2810.4,
      "net": 1689.6,
      "savingsRate": 37.5,
      "averageDailyExpense": 35.58,
      "completedCount": 64,
      "pendingCount": 2
    },
    "monthly": [
      { "month": "2026-09", "revenues": 1500, "expenses": 990.1 },
      { "month": "2026-10", "revenues": 1500, "expenses": 1000 },
      { "month": "2026-11", "revenues": 1500, "expenses": 820.3 }
    ],
    "accounts": [
      {
        "id": "1",
        "name": "Ordenado",
        "type": "bank_account",
        "active": true,
        "balance": 1250.5,
        "reservedAmount": 200,
        "availableAmount": 1050.5,
        "revenues": 4500,
        "expenses": 2810.4,
        "transactionCount": 64
      }
    ],
    "categories": [
      { "id": "12", "name": "Supermercado", "amount": 620.4 }
    ],
    "latestActivity": [
      {
        "id": "381",
        "description": "Continente",
        "type": "expense",
        "amount": 35.2,
        "date": "2026-11-15",
        "status": "completed",
        "categoryName": "Supermercado",
        "account": { "id": "1", "name": "Ordenado" }
      }
    ]
  },
  "errors": null
}
```

- `summary.balance` soma o saldo atual das contas (não depende do período). `income`, `expenses`, `net`, `completedCount` e os totais por conta/categoria contam só `completed` dentro do período.
- `savingsRate` é `null` quando `income` é 0. `averageDailyExpense` = despesas / dias do período.
- `pendingCount` conta as `pending` do período com data até hoje.
- `monthly` tem uma entrada por mês do período, com zeros nos meses sem movimento.
- `categories`: só despesas, ordenadas por montante (desc). `latestActivity`: 8 últimos movimentos (`completed` e `pending`) do período.

**Erros:**
- 422 — `period` fora da lista, `currency` com formato inválido ou que não pertence a nenhuma conta do utilizador (formato Laravel).
- 500 — erro inesperado, com a mensagem `accounts::messages.accounts.resume_error` e `errors: null`.

## Reservas por meta no detalhe da conta

`GET /api/v1/accounts/{id}` acrescenta `data.goalReservations`: lista de `{id, name, status, fundedAmount, usedAmount, reservedAmount}`. Valores numéricos na moeda da conta. Só agrega movimentos completed desta conta: funded = contribution − withdrawal; used = usage; reserved = funded − used. Pendentes não contam. Inclui metas concluídas e reservas esgotadas com histórico.

Exige `viewAccount`; cada meta exige pertença e `viewFinancialGoal`. Não expõe nomes/IDs de metas privadas. `reservedAmount`/`availableAmount` globais da conta continuam a incluir todas as reservas, mesmo as de metas privadas; a lista visível pode portanto não somar o total. Não somar novamente as reservas ao património.
