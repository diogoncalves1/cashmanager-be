# Intents

Uma **intent** regista o *porquê* de um trabalho: o pedido, o problema ou a ideia, antes de se decidir *como* resolver. É curta e escrita do ponto de vista do produto ou do utilizador.

Ciclo: **intent** → [spec](../specs/) (se forem precisas regras ou contratos) → [plan](../plans/) → branch(es) ([git-workflow](../process/git-workflow.md)).

## Nomes dos ficheiros

`INT-<nnn>-<descricao-curta>.md`, por exemplo `INT-001-budgets.md`. O número é sequencial e nunca se reutiliza.

## Estados

`proposta` → `aceite` → `em-curso` → `concluída` (ou `rejeitada` / `adiada`)

## Modelo

```markdown
# INT-nnn — <Título>

| | |
|---|---|
| **Estado** | proposta |
| **Data** | AAAA-MM-DD |
| **Pedido por** | <pessoa/papel> |
| **Ticket** | CM-xxx (se existir) |

## Problema / necessidade
O que falta ou está errado, e para quem.

## Resultado pretendido
O que muda para o utilizador quando isto estiver feito.

## Fora de âmbito
O que explicitamente não se vai fazer.

## Notas / referências
Ligações a conversas, issues, specs existentes.
```

## Índice

| ID | Título | Estado |
|---|---|---|
| — | (ainda sem intents) | — |
