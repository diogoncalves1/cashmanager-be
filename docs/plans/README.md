# Plans

Um **plan** define *como* e *por que ordem* se implementa uma spec ou intent. Divide o trabalho em features pequenas, e cada feature corresponde a uma branch, uma revisão e uma aprovação ([git-workflow](../process/git-workflow.md)).

Ciclo: [intent](../intents/) → [spec](../specs/) → **plan** → branch(es).

## Nomes dos ficheiros

`PLAN-<nnn>-<descricao-curta>.md`, por exemplo `PLAN-001-budgets.md`. Dentro do plano, as features numeram-se `F1`, `F2`, …

## Estados

`rascunho` → `aprovado` → `em-curso` → `concluído`

## Modelo

```markdown
# PLAN-nnn — <Título>

| | |
|---|---|
| **Estado** | rascunho |
| **Data** | AAAA-MM-DD |
| **Spec / Intent** | SPEC-nnn / INT-nnn |

## Objetivo
Uma frase.

## Features (ordem de execução)

| # | Feature | Branch | Depende de | Estado |
|---|---|---|---|---|
| F1 | Migração e entidade | `feature/<descrição>` | — | pendente |
| F2 | Endpoints CRUD | `feature/<descrição>` | F1 | pendente |

### F1 — <nome>
- Ficheiros/módulos a alterar
- Migrações e permissões
- Testes a escrever
- Critério de conclusão

## Riscos e mitigação
Dados históricos, migrações irreversíveis, impacto no frontend.

## Deploy
Ordem das migrações, comandos a correr, flags, rollback.

## Documentação a atualizar
Ficheiros em `api/` e `architecture/`, e o `AGENTS.md`.
```

## Índice

| ID | Título | Estado |
|---|---|---|
| — | (ainda sem planos) | — |
