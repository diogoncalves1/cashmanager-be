# Fluxos financeiros e invariantes

Usar este documento antes de alterar saldos ou estados. Detalhes: [Accounts](modules/accounts.md), [Debts](modules/debts.md), [FinancialGoal](modules/financial-goal.md) e [Recorrentes](modules/recurring-transaction.md).

## O que altera o saldo

| Operação | Registo de conta | Efeito no saldo |
|---|---|---|
| Receita completed | Transaction `revenue` | Soma o montante |
| Despesa completed | Transaction `expense` | Subtrai o montante |
| Transação pending | Transaction pendente | Nenhum até confirmar |
| Editar/apagar transação completed | Transaction existente | Aplica diferença/reverte efeito através de TransactionRepository |
| Pagamento de dívida | DebtPayment ligado a uma Transaction de despesa | Desconta quando completed |
| Contribuir para meta | Movimento `contribution` do ledger | Reserva sem debitar |
| Levantar reserva | Movimento `withdrawal` do ledger | Liberta sem creditar |
| Usar reserva com despesa existente | Movimento `usage` ligado à despesa | A ligação não volta a debitar |
| Usar reserva criando despesa | Movimento `usage` + nova Transaction | Debita uma vez através da despesa |
| Migração histórica de metas | Auditoria/neutralização de transações antigas | Ajuste explícito e único pelo conversor |

`accounts.balance` é persistido; não é calculado em cada leitura. A exceção histórica à escrita por TransactionRepository é o rollout controlado das metas. Não corrigir saldos atualizando apenas uma linha sem reconciliar movimentos e auditoria.

## Saldos, reservas e moedas

- Saldo contabilístico: dinheiro na conta (`balance`).
- Reservado: montante comprometido com metas (`reservedAmount`).
- Disponível: saldo menos reservado (`availableAmount`); o contexto de reservas também expõe insuficiência (`reservationShortfall`).
- Contribuir e levantar reservas não equivale a criar receita/despesa.
- Completar uma meta não cria automaticamente uma despesa.
- Account Resume e resumo de recorrentes mantêm moedas separadas. Stats convertidas usam taxas de `currencies` e podem mudar quando o câmbio muda.
- Não existe uma operação de transferência implementada; não pressupor atomicidade entre duas transações independentes.

## Recorrências

O scheduler despacha `GenerateRecurringTransactionsJob`; se a fila for assíncrona é necessário worker. O gerador percorre datas desde a primeira ocorrência pendente até hoje, respeita `end_date` e atualiza `last_generated_at` após cada criação.

| Ligação | Destino do gerador |
|---|---|
| Sem ligação | `TransactionRepository::store` |
| Dívida | `DebtPaymentRepository::store` → despesa de conta |
| Meta | `FinancialGoalTransactionRepository::store` → ledger; expense mapeia para contribution, revenue para withdrawal |

Uma recorrência de meta não cria uma transação contabilística só por chamar o repository de movimentos. O comportamento final é o do ledger atual. Não reutilizar descrições do modelo antigo em novas integrações.

## Escrita e concorrência

Conservar transações de BD, locks e ordem de aquisição usados no fluxo alterado. As reservas usam lock utilizador → conta → meta, recibos de idempotência e unicidade por recorrência/data. Isso não prova a mesma proteção em todos os fluxos de transações ou dívida.

As flags `actions` são apresentação; o servidor tem de autorizar cada mutação. No estado local, a policy de conta e os nomes de Gate chamados pelo repository têm diferenças registadas em [problemas conhecidos](known-issues.md).

## Alterações que exigem verificação conjunta

| Alteração | Verificar |
|---|---|
| Estado, valor ou eliminação de transação | Saldo, pagamento ligado, utilização de meta, atividade e resumos |
| Categoria | Seleção pessoal/global, arquivo, categorias internas e geração de recorrências |
| Moeda da conta/meta | Histórico, reservas, pickers e totais convertidos |
| Membership/shared role | Listas, detalhe, dashboard, agregados e mutações |
| Migração de metas | [Rollout específico](../../Modules/FinancialGoal/PRODUCTION-ROLLOUT.md), ensaio MySQL e recuperação |

A suite SQLite não valida as views e agregações MySQL da produção; ver [testes](../process/testing.md).
