# API — Dashboard

Endpoint único do módulo `Modules/DashboardCharts`: totais, gráficos e widgets da página inicial. Convenções gerais em [README.md](README.md).

---

### `GET /api/v1/dashboard-overview`

Devolve os indicadores globais do utilizador (receitas, despesas, saldo e variação), as séries para gráficos e os widgets de contas, dívidas, metas e recorrentes. **Permissão:** nenhuma específica. Cada bloco aplica o seu próprio âmbito, descrito abaixo.

Middleware: só `auth:sanctum`. **Não** tem `setlocale`, por isso os textos traduzidos (`statusTranslated`, `currency.name`) saem no locale por defeito da aplicação (`APP_LOCALE`, `en` no `.env.example`) e não no idioma do utilizador. Os exemplos abaixo mostram esse caso.

**Parâmetros de query:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `min_date` | date | não | Só afeta `charts.monthly`: considera transações com `date >= min_date`. As outras séries ignoram-no |

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": {
    "widgets": {
      "accounts": {
        "activeAccounts": 2,
        "netWorth": "3.45 k €",
        "totalRevenues": "12.40 k €",
        "totalExpenses": "8.95 k €",
        "netWorthAmount": 3450.75,
        "totalRevenuesAmount": 12400,
        "totalExpensesAmount": 8949.25,
        "currencyCode": "EUR"
      },
      "debts": [
        {
          "id": 3,
          "name": "Crédito automóvel",
          "totalAmount": 9000,
          "paidAmount": 2500,
          "currencyCode": "EUR",
          "status": "pending",
          "statusTranslated": "Pending",
          "dueDate": "2026-12-05"
        }
      ],
      "goals": [
        {
          "id": 4,
          "name": "Férias",
          "totalAmount": 2000,
          "contributedAmount": 1200,
          "currencyCode": "EUR",
          "status": "in_progress",
          "statusTranslated": "In Progress"
        }
      ],
      "recurring": {
        "activeCount": 5,
        "pausedRecurring": 1,
        "incomeCount": 1,
        "expenseCount": 3,
        "currencySummaries": [
          { "currencyCode": "EUR", "monthlyIncome": 1500, "monthlyExpenses": 790.5, "monthlyReservations": 100 }
        ],
        "upcoming": [
          { "id": 14, "title": "Renda", "type": "expense", "amount": 650, "currencyCode": "EUR", "nextOccurrence": "2026-12-01" }
        ]
      }
    },
    "charts": {
      "monthly": [
        { "month": "10 2026", "revenues": "1500.00000000", "expenses": "1000.00000000" },
        { "month": "11 2026", "revenues": "1500.00000000", "expenses": "820.30000000" }
      ],
      "quarterly": [
        { "quarter": "Q4 2026", "revenues": "3000.00000000", "expenses": "1820.30000000" }
      ],
      "annualy": [
        { "year": 2026, "revenues": "12400.00000000", "expenses": "8949.25000000" }
      ],
      "userTotal": [
        { "monthYear": "10 2026", "balance": "2770.45000000" },
        { "monthYear": "11 2026", "balance": "3450.15000000" }
      ]
    },
    "dashboard": {
      "revenueClasses": "success",
      "totalRevenues": 12400,
      "totalRevenuesFormated": "12.40 k €",
      "totalUser": "3.45 k €",
      "revenuePercentage": "+13.76",
      "expensesClasses": "error",
      "expensesPercentage": "+10.09",
      "totalExpenses": 8949.25,
      "totalExpensesFormated": "8.95 k €",
      "totalClasses": { "class": "success", "icon": "up" },
      "totalPercentage": "+24.55",
      "currency": {
        "id": "1",
        "name": "Euro",
        "symbol": "€",
        "rate": 0.9234,
        "code": "EUR"
      }
    }
  },
  "errors": null
}
```

#### `dashboard`

Âmbito: transações `completed` **criadas pelo próprio utilizador** (`transactions.user_id`) em contas ativas, convertidas para a moeda preferida (`amount × rate_utilizador / rate_conta`). Não usa membership nem permissões, por isso ignora as transações de outros membros em contas partilhadas.

- `totalRevenues` / `totalExpenses`: totais acumulados de sempre (float). `totalUser` = receitas − despesas, já formatado (string).
- `*Percentage`: variação, em string, entre o total acumulado até **há um mês** (`hoje − 1 mês`) e o total acumulado até hoje. Não compara o mês atual com o anterior.
  - O valor vem de `number_format(…, 2)` com prefixo `+` quando ≥ 0, por exemplo `"+13.76"` ou `"-4.10"`. Acima de 1000 leva separador de milhares (`"+1,250.00"`).
  - Quando o valor antigo é 0, a "percentagem" é `total × 100`.
- `revenueClasses`: `success` (> 0), `warning` (= 0) ou `error` (< 0).
- `expensesClasses`: invertido; uma descida de despesas é `success`.
- `totalClasses` é um **objeto** `{class, icon}` (com `icon` `up`/`left`/`down`), ao contrário das outras duas, que são só a string da classe.
- `currency`: `CurrencyResource` da moeda preferida do utilizador.

#### `charts`

Mesmo âmbito do bloco `dashboard` (transações `completed` do próprio utilizador, em contas ativas, convertidas). Os valores vêm diretamente do SQL, sem cast: `revenues`, `expenses` e `balance` chegam normalmente como strings decimais (dependem do driver). O frontend deve convertê-los para número.

- `monthly`: `month` = `"M YYYY"` (mês sem zero à esquerda, ex.: `"3 2026"`), ordem cronológica.
- `quarterly`: `quarter` = `"Qn YYYY"`.
- `annualy`: `year` (inteiro).
- `userTotal`: saldo acumulado (receitas − despesas) no fim de cada mês com movimento. `monthYear` = `"M YYYY"`.

#### `widgets`

- `accounts`: o mesmo bloco `stats` de `GET /api/v1/accounts` (ver [accounts.md](accounts.md#get-apiv1accounts)). Só conta contas onde o role tem `updateUserBalance`, convertidas para a moeda do utilizador.
- `debts`: até 3 dívidas `pending` onde o utilizador é membro, ordenadas por `dueDate` (asc).
- `goals`: até 3 metas `in_progress` onde o utilizador é membro, ordenadas por progresso (`contributedAmount / totalAmount`, desc).
- `recurring`: o mesmo resumo de `GET /api/v1/recurrings?includeSummary=1` (ver [recurring-transactions.md](recurring-transactions.md#get-apiv1recurrings)): totais mensais normalizados por moeda e próximas ocorrências (7 dias, máx. 5).

**Erros:**
- 401 — sem token.
- 500 — qualquer exceção (o controller força sempre o código 500), por exemplo se o utilizador não tiver preferências ou moeda definida.
