# Testes e verificação

## Organização obrigatória

Testes de um domínio ficam junto do módulo: `Modules/<Module>/Tests/Feature/` para endpoints/fluxos e `Modules/<Module>/Tests/Unit/` para cálculos/regras isoladas. Namespaces seguem `Modules\<Module>\Tests\Feature` ou `Unit`. Cada módulo com testes tem uma testsuite explícita em `phpunit.xml`. Não colocar testes de contas, transações, dívidas, metas, categorias ou recorrências na pasta global.

`tests/Feature` e `tests/Unit` ficam para infraestrutura partilhada: contrato ModulePaginate, fronteira API/DataTables, traduções transversais, autenticação base e health check. Fixtures/classes partilhadas ficam em `tests/Support`.

```bash
composer test
php artisan test --testsuite=Accounts
php artisan test --testsuite=FinancialGoal
php artisan test --testsuite=RecurringTransaction
php artisan test Modules/Debts/Tests/Feature/DebtPaymentPaginationTest.php
php artisan test --filter=PersonalCategoriesTest
```

`composer test` limpa a configuração em cache e executa todas as suites, incluindo módulos. SQLite é em memória; cache/session/mail array; filas sync; Telescope desligado. A configuração de cobertura inclui app/, não representa cobertura integral dos módulos.

## Cobertura relevante

| Âmbito | Testes no módulo |
|---|---|
| Accounts | AccountResume, AccountPagination, TransactionPagination, AccountInvitationPagination |
| Debts | DebtPagination, DebtPaymentPagination, DebtInvitationPagination |
| FinancialGoal | GoalReservations, GoalPagination, GoalTransactionPagination, GoalInvitationPagination |
| RecurringTransaction | RecurringListSummary, RecurringTransactionValidation (Feature); RecurringSummary, RecurringPresentation (Unit) |
| Category | PersonalCategories |
| Friends | SocialPagination e FriendRequestPagination |
| User | UserSearchPagination |
| Notification | NotificationPagination |
| ActivityLog | ActivityPagination |
| SharedRoles | SharedRolePagination |
| Currency / Language | CurrencyCache / LanguageCache |

Os testes de listagem cobrem pertença/permissão, filtros zero e intervalos, pesquisa literal e agrupada, validação 422, paginação/contagens, convites, dados públicos, reservas e união de notificações com IDs repetidos. Os testes de infraestrutura ficam em ModulePaginationTest, ApiListingArchitectureTest e TranslationCatalogueTest.

## Limites e registo

SQLite não comprova views MySQL, DATE_ADD/DATE_FORMAT, ONLY_FULL_GROUP_BY, locks ou concorrência. Alterações de migrações/saldos exigem ensaio MySQL 8 isolado. Não executar migrations de produção para adaptar testes. Documentar comando, ambiente, resultado e limites reais em cada PR; nunca apresentar um teste recomendado como executado.
