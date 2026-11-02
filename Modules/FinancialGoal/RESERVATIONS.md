# Metas financeiras: rollback e alternativa (CM-120 / CM-250)

Atualizado em 2026-11-02. Branch BE: `feature/CM-120`; FE: `feature/CM-250`.

## Estado

O utilizador pediu o rollback da implementação de reservas após falhas sucessivas na conversão dos dados antigos. O código regressou ao modelo anterior de contribuições/levantamentos, incluindo formulários, recorrentes, relatórios e contratos API. Foram retiradas do código ativo as quatro migrations de reservas/conversão, serviços, recibos, endpoints e testes exclusivos dessa tentativa. O histórico Git preserva a implementação anterior até `de590b8`.

Este rollback é de código. Não foram executadas operações sobre a base de dados de produção. Remover um ficheiro de migration não desfaz migrations já aplicadas. Não executar `migrate:rollback`, apagar recibos/auditorias ou repor saldos indiscriminadamente: podem existir campos/tabelas adicionais, conversões concluídas ou movimentos sem `transaction_id` criados pelo novo fluxo. A migration de recibos podia concluir mesmo quando a conversão financeira falhava.

Antes de publicar o código anterior, identificar as migrations aplicadas e verificar se existem metas em modo de reserva ou movimentos sem transação original. Se houve conversões concluídas, a reversão dos dados exige reconciliação própria com o histórico e movimentos posteriores; não restaurar cegamente saldos de snapshots. Os scripts SQL abaixo são de leitura e não certificam, por si só, que o código antigo pode operar sobre dados convertidos.

## A ligação já existe

O modelo antigo guarda esta relação:

```text
financial_goal_transactions.financial_goal_id -> financial_goals.id
financial_goal_transactions.transaction_id   -> transactions.id
transactions.account_id                      -> accounts.id
```

`FinancialGoalTransaction::transaction()` e a view original `financial_goal_transaction_view` já usam esta ligação. A conta de origem de um movimento antigo pode ser consultada sem adicionar `account_id`, inventar `request_key`/`request_hash` ou criar recibos. Recibos protegem a repetição de pedidos; não determinam a origem do dinheiro nem corrigem diferenças de valores.

O ficheiro [`history-account-links.sql`](history-account-links.sql) contém consultas independentes de leitura para:

1. Identificar quais das migrations retiradas foram aplicadas.
2. Mostrar cada movimento, a transação, a conta e inconsistências de tipo/estado/valor/moeda/categoria.
3. Agrupar contribuições menos levantamentos concluídos por meta e conta, incluindo origens desconhecidas.
4. Comparar o total guardado na meta com o total do histórico, sem multiplicar linhas por participantes/permissões.

Usa apenas campos do modelo original. O saldo histórico por conta é origem contabilística, não prova de que o dinheiro continua nessa conta. Pendentes não entram no total concluído. Diferenças ou origens desconhecidas são mostradas, nunca atribuídas automaticamente a outra conta.

## Alternativa proposta para a próxima implementação

Separar a recuperação da conta da mudança contabilística:

1. Preservar as transações antigas e reutilizar a ligação existente para apresentar a origem por conta. Se faltarem ligações ou os totais divergirem, identificar os movimentos/valores concretos; não impedir a leitura de todas as outras metas nem exigir um backfill global de recibos.
2. Para novas reservas, guardar movimentos próprios ligados à conta, sem criar uma despesa. Não converter implicitamente histórico antigo a cada consulta. O saldo da conta continua informativo, mesmo negativo; manter a preferência expressa pelo utilizador.
3. Para transitar uma meta antiga, usar um ponto de corte e um lançamento de abertura reconciliado por conta. Preservar a contribuição antiga, a transação original e a evidência do ajuste. A reposição do antigo débito deve ser um ajuste explícito, auditável e aplicado uma única vez após reconciliação, conforme a política já escolhida pelo utilizador. Não eliminar transações nem impor um saldo atual retirado de um snapshot.
4. Para a compra, guardar uma associação separada à despesa real (`goal_id`, `transaction_id`, valor utilizado). Associar uma despesa já existente não cria outro débito; a soma das associações não excede a despesa. Atingir 100% não cria uma transação automaticamente.

Exemplo: 2.000 € de contribuições antigas podem ser identificados pela conta das respetivas transações. Como essas contribuições já baixaram o saldo, apenas ligar uma nova despesa de 2.000 € duplicaria o débito. Primeiro é necessário reconciliar a abertura/ajuste; só depois se regista ou associa a compra e se consome a reserva. Esta alternativa ainda não está implementada.

## Verificação do rollback

Verificar tipos e regressões de categorias/recorrentes no FE; categorias, traduções, recorrentes e resumo de contas no BE. Testes usam SQLite isolado. Ensaiar o SQL de diagnóstico numa cópia de produção para conhecer os casos reais antes de definir outra migration.

## Validação feita nesta reversão

- BE: 34 testes passaram (2.429 asserções), cobrindo categorias, traduções, recorrentes e resumo de contas.
- FE: verificação TypeScript passou; 16 testes de categorias e 7 de recorrentes passaram. Foi removida uma referência gerada obsoleta em `.next/types` para a rota de reservas retirada.
- As quatro consultas SQL foram verificadas em SQLite isolado com origens conhecidas/desconhecidas, pendentes, histórico líquido negativo, moedas/valores inconsistentes e metas sem movimentos. Ainda não foram executadas nos dados de produção nem ensaiadas na versão MySQL de destino.

## Divergência do valor reservado relatada pelo utilizador

O utilizador indicou que o valor reservado não estava a ser registado corretamente; isto não confirma quais migrations terminaram nem se houve utilização do novo fluxo. Não assumir o estado dos dados de produção.

Na revisão do modelo anterior foram encontrados três registos que podem divergir: `financial_goals.contributed_amount`, o líquido dos movimentos de `financial_goal_transactions` e o líquido das transações internas de conta associadas. O SQL compara os três, além de mostrar diferenças em cada movimento. O `TransactionRepository` anterior edita/confirma/elimina a transação de conta sem atualizar o movimento/total da meta; o percurso inverso, através de `FinancialGoalTransactionRepository`, atualiza ambos. Isto é uma origem possível de divergências, demonstrável no código, mas não prova a causa nos dados do utilizador. Uma futura implementação deve ter um único percurso consistente para operações sobre transações ligadas e preservar as permissões dos dois domínios.
