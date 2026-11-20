# Specs

Uma **spec** define *o quê* com precisão: regras de negócio, contratos de API, modelo de dados e casos-limite de uma funcionalidade. É a referência para implementar e para rever.

Ciclo: [intent](../intents/) → **spec** → [plan](../plans/) → branch(es).

## Nomes dos ficheiros

`SPEC-<nnn>-<descricao-curta>.md`, por exemplo `SPEC-001-budgets.md`. Quando nasce de uma intent, referencia o `INT-nnn` correspondente.

## Estados

`rascunho` → `em-revisão` → `aprovada` → `implementada` (ou `substituída por SPEC-xxx`)

## Modelo

```markdown
# SPEC-nnn — <Título>

| | |
|---|---|
| **Estado** | rascunho |
| **Data** | AAAA-MM-DD |
| **Intent** | INT-nnn |
| **Módulos afetados** | Accounts, ... |

## Contexto
Resumo do problema (ligar à intent).

## Regras de negócio
Lista numerada e verificável (R1, R2, ...).

## Modelo de dados
Tabelas, colunas, views, migrações, permissões novas.

## API
Endpoints novos ou alterados: método, rota, body, resposta, erros (seguir o formato de ../api/).

## Casos-limite e erros
Concorrência, moedas diferentes, permissões, dados históricos.

## Critérios de aceitação
O que tem de ser verdade para dar a spec como implementada (inclui testes).

## Decisões em aberto
Perguntas por responder e quem decide.
```

## Specs existentes noutros locais

Estas specs são anteriores a esta pasta e ficam onde estão:

| Tema | Local |
|---|---|
| Categorias pessoais | `Back/CashManager/Modules/Category/README.md` |
| Reservas das metas | `Back/CashManager/Modules/FinancialGoal/RESERVATIONS.md` |
| Rollout das reservas em produção | `Back/CashManager/Modules/FinancialGoal/PRODUCTION-ROLLOUT.md` |
| Budgets (planeamento) | `Front/CashManager/BUDGETS.md`, `BUDGETS-EXAMPLES.md`, `BUDGETS-ARCHITECTURE.md`, `BUDGETS-PROTOTYPE.md` |
| Categorias (frontend) | `Front/CashManager/CATEGORIES.md` |
| Metas (frontend) | `Front/CashManager/FINANCIAL-GOALS.md` |

## Índice

| ID | Título | Estado |
|---|---|---|
| — | (ainda sem specs nesta pasta) | — |
