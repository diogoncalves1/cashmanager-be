# Reservas de metas — CM-120 / CM-250

Implementação refeita em 2026-11-02 nas branches `feature/CM-120` (BE) e `feature/CM-250` (FE).

## Regras atuais

- Novas metas usam `funding_mode=reservation`. Reservar/libertar não cria transação nem altera o saldo. Saldo zero/negativo e disponível negativo são informativos, sem avisos de insuficiência ou bloqueios.
- Uma meta pode reservar em várias contas da mesma moeda. Libertar/utilizar está limitado à reserva da meta nessa conta; não fazer conversão cambial implícita.
- Utilização cria uma despesa concluída e categorizada ou associa parte de uma despesa existente. A associação não debita outra vez; a soma das associações é limitada ao valor da despesa.
- Pendentes só contam depois da confirmação na data devida ou posterior. Conclusão manual não cria despesa nem elimina reservas; progresso distingue financiamento, reserva e utilização. Uma meta concluída pode utilizar/libertar o restante.
- Reservas/libertações concluídas são corrigidas com movimentos opostos. Eliminar uma utilização desassocia-a, preservando a despesa real e repondo a reserva. Desassociar antes de alterar/eliminar a despesa.
- Permissões da meta e da conta são verificadas. Locks seguem utilizador, conta e meta. Chaves de repetição e recibos protegem novas operações, incluindo a repetição de uma compra já desassociada. Recorrentes usam identidade de ocorrência.
- O valor reservado deriva dos movimentos concluídos; não depende exclusivamente do total guardado em cache na meta. Recorrentes em modo de reserva ficam separados de receitas/despesas.

## Migração refeita

Consultar [PRODUCTION-ROLLOUT.md](PRODUCTION-ROLLOUT.md) para os comandos, política dos valores, compatibilidade, auditoria e recuperação.

A associação histórica usa `financial_goal_transactions.transaction_id -> transactions.account_id`. As transações originais e as ligações são preservadas. Os antigos movimentos internos são neutralizados para relatórios e os respetivos efeitos no saldo são revertidos uma única vez, de forma auditada e atómica por meta.

O valor/estado/data da transação original serve para corrigir movimentos e totais antigos desatualizados. O relatório mostra as diferenças e o snapshot preserva os valores anteriores. Não se fabricam chaves/hashes/recibos para ligar o histórico às contas.

Casos não reconciliáveis ficam `legacy` e aparecem em `needs_review`, sem abortar as restantes metas. Continuam disponíveis através do fluxo anterior, que afeta o saldo. A alteração isolada da transação normal ligada é recusada para evitar dessincronizar a meta. O novo frontend mantém os formulários antigos nesses casos e explica o efeito no saldo.

## API e ferramentas

- `GET /api/v1/financial-goals/{id}/reservations`: resumo por conta e valores de reserva/utilização/financiamento.
- `GET /api/v1/financial-goals/{id}/eligible-expenses`: despesas concluídas elegíveis, paginadas por conta.
- `POST /api/v1/financial-goal-transactions`: contribuição, levantamento ou utilização. Uso pode receber `transaction_id` existente ou `category_id` para criar uma despesa; novos pedidos de reservas exigem `request_key`.
- Atualização/eliminação/confirmação e ciclo de vida mantêm os endpoints existentes, com HTTP 422 e erros por campo.
- `php artisan goals:reservation-rollout`: pré-visualização de leitura, inclusive antes da preparação do esquema; `--goal=1` filtra uma meta; `--apply` aplica casos reconciliados; `--fail-on-review` permite validação automatizada estrita.
- Migrations novas: `210000_prepare_goal_reservation_rollout` e `220000_migrate_goal_reservations_preserving_transactions`. Não executar novamente as quatro migrations da tentativa retirada.
- O SQL `history-account-links.sql` mantém-se como diagnóstico adicional do modelo antigo; movimentos de utilização do novo modelo são identificados como não pertencentes ao modelo antigo.

## Verificação

Testes: `php artisan test --filter='GoalReservationsTest|TranslationCatalogueTest|PersonalCategoriesTest|RecurringSummaryTest|AccountResumeTest'`. FE: `npm run test:goals`, `npm run test:categories`, `npm run test:recurring`, `npm run typecheck`, `npm run build`.

DDL e conversão foram também ensaiados em MySQL 9.6.0 isolado, com esquema original e esquema instalado pela tentativa anterior, repetindo preparação/conversão sem duplicar saldos ou recibos. Antes da publicação, ensaiar uma cópia real na versão do servidor de destino. Nenhuma base de produção foi alterada nesta tarefa.
