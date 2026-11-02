# Entrega de reservas das metas — CM-120 / CM-250

## O que muda

Reservar e libertar dinheiro mantém o saldo da conta. Utilizar cria uma despesa real ou associa uma já existente sem outro débito. O saldo pode ser negativo. Atingir o objetivo ou concluir a meta não cria uma despesa.

A migração descobre a conta através de `financial_goal_transactions.transaction_id -> transactions.account_id`. Mantém a transação original, a ligação e um snapshot dos dados anteriores. Os movimentos internos antigos passam a constar de `financial_goal_neutralized_transactions`, ficando fora das listagens e dos cálculos de receitas/despesas. Continuam consultáveis pelo histórico da meta e para auditoria. O saldo atual das contas é apresentado independentemente da existência de transações visíveis.

Para cada transação interna concluída, repõe o débito de uma contribuição ou desfaz o crédito de um levantamento. Estes ajustes e a passagem da meta para reservas são gravados na mesma transação de BD. O registo por transação impede uma segunda reposição. Não substitui o saldo atual pelo saldo antigo de um snapshot.

## Origem dos valores

O valor, estado, data e conta da transação original são a fonte para reconciliar movimentos antigos. Esse registo é o que determinou o efeito no saldo. Se o movimento da meta ou o seu total estiverem desatualizados, são corrigidos a partir dessa origem, ficando os valores anteriores no snapshot e as diferenças no relatório.

Exemplo de ensaio: meta com total guardado de 999 €, movimento de 80 € e transação original de 125,25 €. A reserva passa a 125,25 €, na conta da transação. O ajuste dessa conta é +125,25 €, uma vez. O relatório identifica a correção de -873,75 € no total e preserva os três valores anteriores. Não cria recibos para justificar a associação à conta.

Transações reais de compras, ligações duplicadas, moedas incompatíveis, movimentos sem transação, metas financiadas sem histórico e líquidos negativos por conta não são convertidos por suposição. A meta fica `legacy`, com relatório `needs_review`, mantendo as operações anteriores através da meta. Os outros objetivos são migrados. Um relatório com `historyComplete=false` devolve `fundedAmount`/`difference` nulos, pois não existe uma soma reconciliada completa.

No modelo anterior, as contribuições/levantamentos continuam a afetar o saldo. O frontend explica essa diferença e mantém o formulário disponível. A utilização de reservas fica disponível depois da reconciliação. Editar/confirmar/apagar uma transação interna isoladamente pela área de transações é recusado; fazê-lo através da meta atualiza os dois registos.

## Compatibilidade com as tentativas anteriores

- As duas migrations novas têm nomes diferentes das quatro retiradas. Não apagar registos da tabela `migrations` para tentar forçar uma nova execução.
- A preparação adiciona apenas os campos/tabelas em falta e recria a view dos movimentos. Suporta o esquema original e o esquema de reservas já instalado.
- `request_key`, `request_hash`, recibos e auditorias já existentes são preservados. Não existe backfill obrigatório de recibos para o histórico.
- Metas já em `reservation` não recebem novos ajustes de saldo. O total derivado é reconciliado com o seu ledger de reservas, preservando os dados de auditoria existentes.
- As transações eliminadas por uma conversão anterior que tenha concluído não são recriadas automaticamente. Essa versão deixou cópias em `financial_goal_conversions`; a nova versão preserva essas auditorias e os movimentos já convertidos.

Confirmar que o deployment remove efetivamente os ficheiros antigos, incluindo em servidores que copiam ficheiros sem eliminar os que desapareceram do Git:

```text
2026_11_01_180000_add_goal_reservations.php
2026_11_02_180000_backfill_financial_goal_accounts.php
2026_11_02_185000_backfill_financial_goal_request_receipts.php
2026_11_02_190000_convert_legacy_goal_reservations.php
```

## Procedimento de produção

Ensaiar primeiro numa cópia recente de produção, na mesma versão MySQL/MariaDB e com tabelas InnoDB. A validação local com dados fictícios não substitui esse ensaio. Guardar um backup completo e os relatórios; contêm valores financeiros e devem ficar fora de diretórios públicos.

1. Ativar manutenção e suspender também workers/agendamentos que geram movimentos. Publicar o novo BE sem permitir escritas financeiras durante a entrega.
2. Fazer a pré-visualização, que é só de leitura e funciona antes de alterar o esquema:

```sh
php artisan goals:reservation-rollout
```

Para investigar apenas uma meta:

```sh
php artisan goals:reservation-rollout --goal=1
```

3. Aplicar a preparação e a conversão, por esta ordem:

```sh
php artisan migrate --path=Modules/FinancialGoal/Database/Migrations/2026_11_02_210000_prepare_goal_reservation_rollout.php --force
php artisan migrate --path=Modules/FinancialGoal/Database/Migrations/2026_11_02_220000_migrate_goal_reservations_preserving_transactions.php --force
```

4. Consultar novamente o estado:

```sh
php artisan goals:reservation-rollout
```

Metas convertidas aparecem como `already_reservation` e não recebem outro ajuste. Os relatórios da aplicação ficam em `financial_goal_reservation_rollouts`, com snapshot anterior, valores por conta, ajustes e correções. `needs_review` identifica casos pendentes e os movimentos concretos; não significa que as outras metas falharam. Confirmar os saldos, reservas por conta e a separação de receitas/despesas.

5. Depois de reconciliar um caso pendente, pré-visualizar e aplicar apenas essa meta:

```sh
php artisan goals:reservation-rollout --goal=1
php artisan goals:reservation-rollout --goal=1 --apply
```

O comando sem `--apply` nunca grava. `--fail-on-review` é opcional para verificações automatizadas com código de saída não zero perante casos pendentes.

6. Publicar o FE correspondente, renovar as caches da aplicação conforme o deployment habitual, retomar workers/agendamentos e desativar a manutenção. Recarregar sessões abertas para usar o novo formulário com chaves de repetição.

## Interrupções e recuperação

A preparação contém DDL, que no MySQL não é uma transação única; pode ser repetida após resolver um erro de esquema. A conversão é atómica por meta. Se uma falha inesperada interromper o processo, as metas já concluídas ficam marcadas; repetir o comando ou a migration não repõe esses saldos outra vez. Uma falha na escrita do saldo anula também as alterações do movimento e da neutralização dessa meta.

As migrations são deliberadamente `forward-only`: `down()` recusa apagar reservas ou desfazer saldos sem reconciliação. Não usar `migrate:rollback` para esta entrega. Antes de novas escritas, a recuperação pode usar o backup coordenado com a versão de código correspondente. Depois de novas escritas, corrigir para a frente ou reconciliar os movimentos posteriores; não sobrepor saldos atuais com snapshots antigos.

## Verificação realizada

- Testes isolados cobrem o ciclo do carro, saldo negativo, cêntimos, permissões, pendentes, associação de despesa, repetição de pedidos e de migração, correções de totais, preservação de transações/recibos, casos pendentes e falha atómica de saldo.
- Ensaio em MySQL **9.6.0** local, sem rede e com dados fictícios: esquema original e esquema da tentativa anterior; preparação duas vezes e conversão duas vezes em cada cenário. Ligações, saldos, reservas e recibos foram verificados.
- O frontend passou o build de produção, a verificação TypeScript e os testes de metas/categorias/recorrentes. Nenhuma migration foi executada na base de dados real de produção nesta tarefa.
