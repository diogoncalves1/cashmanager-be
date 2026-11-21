# Produção: modelo único de reservas (CM-120 / CM-250)

Atualizado em 2026-11-02. Este procedimento substitui a entrega anterior, que distinguia metas por `funding_mode`. A aplicação passa a ter um único ledger para todas as metas; a migration remove esse campo e o contrato API deixa de expor `fundingMode`.

## Correção do histórico misto

A conversão examina cada movimento, independentemente de qualquer classificação anterior da meta. Uma meta pode conter movimentos históricos sem `account_id`, reservas recentes sem transação e utilizações ligadas a despesas reais. Todos entram no mesmo cálculo.

Caso de regressão analisado: 22 movimentos concluídos, com 131,40 € de contribuições, 10 € de levantamentos e nenhuma utilização. O reservado é **121,40 €**. Os 10 € recentes não substituem os 21 movimentos históricos cuja conta ainda está por preencher.

- O ledger da meta é a fonte dos valores/estados/datas da reserva. Não sobrescrever esses valores com o montante da transação de conta.
- A transação original identifica a conta e o efeito contabilístico a neutralizar. O ajuste do saldo usa o valor/estado reais dessa transação; se diferirem do movimento da meta, as diferenças ficam no relatório.
- A ligação exclusiva de uma contribuição/levantamento à transação identifica a sua origem interna, mesmo que a categoria tenha sido editada. Tipo de transação incompatível, referência partilhada com outra obrigação e origem não recuperável bloqueiam a conversão.
- As transações e ligações originais são preservadas. As neutralizadas deixam de contar como receitas/despesas. `financial_goal_neutralized_transactions` evita repor o mesmo débito/crédito duas vezes.
- Reservas recentes já diretamente associadas a uma conta não recebem ajuste de saldo. Despesas de utilização não são reembolsadas nem neutralizadas.
- Auditorias de uma conversão anterior podem recuperar a conta de transações que essa versão eliminou. A política de reposição registada nessa auditoria é respeitada para não repor dinheiro outra vez.
- Recibos e chaves existentes são preservados. Não é necessário fabricar recibos para recuperar a conta.

A conversão financeira é **atómica para todas as metas selecionadas**: ou converte o conjunto completo ou não grava nenhuma alteração financeira. Não há metas mantidas num segundo modelo. Uma origem em falta faz a migration falhar com identificação da meta/movimento; deve ser reconciliada explicitamente, sem atribuir uma conta por suposição. A pré-visualização assinala esses casos como `blocked`.

## Antes de executar no servidor

Publicar primeiro a versão corrigida de BE e FE, guardar um backup completo e ativar manutenção. Suspender também workers/agendamentos que geram movimentos. Ensaiar previamente uma cópia recente da BD de produção, com tabelas InnoDB. A pré-visualização só lê dados:

```sh
php artisan goals:reservation-rollout
php artisan goals:reservation-rollout --goal=7
```

Os relatórios têm dados financeiros: guardá-los fora de diretórios públicos.

## Comandos de migração

Executar, por esta ordem, na raiz do backend:

```sh
php artisan migrate --path=Modules/FinancialGoal/Database/Migrations/2026_11_02_210000_prepare_goal_reservation_rollout.php --force
php artisan migrate --path=Modules/FinancialGoal/Database/Migrations/2026_11_02_230000_unify_all_goal_reservations.php --force
```

A `230000` também prepara os campos/tabelas em falta, converte o histórico misto e elimina `funding_mode`. Funciona quando a `210000` e/ou a `220000` da entrega anterior já estão registadas como executadas; não é necessário nem adequado fazer rollback delas. Se forem usadas as migrations do módulo em conjunto, a `220000` usa o mesmo conversor atómico e a repetição é segura.

Não voltar a instalar/executar as quatro migrations retiradas (`2026_11_01_180000`, `2026_11_02_180000`, `185000`, `190000`). Confirmar que esses ficheiros antigos foram removidos do servidor, inclusive em deploys que copiam ficheiros sem eliminar os retirados do Git.

## Verificação e reabertura

Depois de ambas concluírem:

```sh
php artisan goals:reservation-rollout --fail-on-review
php artisan goals:reservation-rollout --goal=7
```

Uma segunda leitura deve mostrar `unchanged`, sem origens em falta. Para os movimentos analisados, a meta 7 deve apresentar 121,40 € reservados, distribuídos pelas contas das transações originais. Se a BD real tiver movimentos adicionais, confirmar o respetivo líquido no relatório.

A auditoria resumida fica em `financial_goal_reservation_rollouts`; cada aplicação adicional guarda um evento e snapshot em `financial_goal_reservation_rollout_events`, preservando também o snapshot inicial. Confirmar saldos, reservas por conta e exclusão das transações internas das despesas. Só depois reativar workers/agendamentos e executar `php artisan up`. Recarregar a interface para obter o novo contrato.

## Falhas e recuperação

Se a conversão financeira falhar, não reabrir a aplicação. Consultar a pré-visualização, corrigir a origem apontada e repetir a migration. Nenhuma outra meta é deixada parcialmente convertida por essa execução. A preparação e remoção do campo são DDL, que no MySQL não formam uma única transação com os dados; esses passos são repetíveis. Se o erro ocorrer no DDL após a conversão financeira, os registos por transação continuam a impedir reposições duplicadas.

As migrations são `forward-only`; `down()` recusa uma reversão cega de saldos. Não usar `migrate:rollback` nem apagar linhas da tabela `migrations` para repetir a conversão. Antes de novas escritas, recuperar com um backup coordenado com a versão de código; depois de novas escritas, reconciliar os movimentos posteriores ou corrigir para a frente.

## Validação realizada

- Teste sanitizado com os 22 valores do caso da meta 7, incluindo 21 contas por recuperar e uma reserva recente: 121,40 € no total e por conta; repetição sem outra reposição.
- Ensaios adicionais em **MySQL 8.0.40 isolado**, a versão indicada no export: esquema original e esquema da tentativa anterior, executando a `230000` duas vezes. Confirmadas contas, saldo, transações preservadas, recibos e eliminação do campo de modo.
- Testes BE cobrem atomicidade entre metas, valores da meta diferentes da transação, pendentes, utilizações, chaves, auditorias anteriores e saldos negativos. FE verifica o contrato sem modo, traduções, erros, tipos e build.
- Nenhuma alteração foi executada em bases de dados reais de produção nesta tarefa.

## Conferência desta entrega — 21/11/2026

A discriminação `goalReservations` no detalhe da conta não precisa de outra migration. Depende do ledger e das migrations acima, que têm de ser publicadas com os consumidores correspondentes. Os testes desta tarefa foram executados em SQLite em memória; o ensaio MySQL referido acima pertence à implementação anterior e não foi repetido nesta revisão. A BD real de produção não foi alterada.

### Exemplo de aceitação

Conta Teste com saldo total de 1.000 € e meta Férias sem movimentos:

| Operação concluída | Saldo total | Disponível | Reservado em Férias | Utilizado em Férias | Contribuído líquido |
|---|---:|---:|---:|---:|---:|
| Contribuir 100 € | 1.000 € | 900 € | 100 € | 0 € | 100 € |
| Levantar 20 € | 1.000 € | 920 € | 80 € | 0 € | 80 € |
| Gastar 30 € criando despesa | 970 € | 920 € | 50 € | 30 € | 80 € |

A redução ao contribuir aplica-se ao **disponível**, mantendo o dinheiro identificado na conta. Uma utilização reduz tanto o saldo total como a reserva; não volta a reduzir o disponível. Associar uma despesa existente também consome a reserva, mas não volta a debitar o saldo total. Pendentes só passam a contar após confirmação.

### Dados antigos e reconciliação

Antes do deploy, guardar o relatório completo da pré-visualização e o backup. Para cada conta, conferir os ajustes de todas as metas no relatório: uma conta pode financiar várias metas, pelo que se somam os `balanceAdjustment` dessas metas. Não usar o `balanceAfter` de uma única meta como saldo final dessa conta quando há várias metas no mesmo relatório. Confirmar separadamente a soma das reservas por conta e por meta.

Se a origem de um movimento não for recuperável, manter manutenção e reconciliar com a transação original/backup; não inventar uma conta. Depois da conversão, repetir `goals:reservation-rollout --fail-on-review`: deve sair sem erro e sem ajustes pendentes. Confirmar que os débitos/créditos internos neutralizados deixam de contar como consumo/receita e que as despesas reais se mantêm.

Na interface, conferir a conta e a meta com o mesmo utilizador autorizado: os valores da secção “Metas nesta conta” devem corresponder aos da conta na meta. Utilizadores sem acesso a uma meta só veem a reserva global da conta, sem nome/ID da meta. Efetuar o exemplo numa conta de teste do ambiente de ensaio antes de reabrir produção.

### Verificações executadas nesta revisão

- `composer test`: 114 testes passaram, 3160 asserções (SQLite em memória).
- Suite FinancialGoal: 32 testes passaram, 216 asserções. Inclui migração atómica, repetição sem duplicar reposições, origens em falta e o novo detalhe por conta.
- Laravel Pint e `git diff --check`: sem erros.
- FE: 14 testes de metas passaram; TypeScript e build de produção passaram.
- Falha de ferramenta existente no FE: `npm run check:i18n` não executou a auditoria porque `scripts/check-translations.cjs` não existe (`MODULE_NOT_FOUND`). O teste de paridade PT/EN das metas passou.
- Não foi executado deploy, merge, migração em produção nem novo ensaio MySQL nesta revisão.
