# Migração modular das listagens

Pedido de 20/11/2026. Nova arquitetura em [ModulePaginate, ModuleFilter e ModuleSearch](../architecture/module-listings.md).

Todas as issues pertencem ao projeto [Cash Manager](https://github.com/users/diogoncalves1/projects/2), campo Version = Version 1.6.2 para esta entrega. PRs aguardam confirmação explícita do responsável; não integrar nem ativar auto-merge.

## Divisão do backend

| Issue | Âmbito | Branch |
|---|---|---|
| [#121](https://github.com/diogoncalves1/cashmanager-be/issues/121) | listing-core | `refactor/api-listing-core` |
| [#122](https://github.com/diogoncalves1/cashmanager-be/issues/122) | accounts | `refactor/accounts-pagination` |
| [#123](https://github.com/diogoncalves1/cashmanager-be/issues/123) | transactions | `refactor/transactions-pagination` |
| [#124](https://github.com/diogoncalves1/cashmanager-be/issues/124) | account-invitations | `refactor/account-invitations-pagination` |
| [#125](https://github.com/diogoncalves1/cashmanager-be/issues/125) | activity | `refactor/activity-pagination` |
| [#126](https://github.com/diogoncalves1/cashmanager-be/issues/126) | debts | `refactor/debts-pagination` |
| [#127](https://github.com/diogoncalves1/cashmanager-be/issues/127) | debt-payments | `refactor/debt-payments-pagination` |
| [#128](https://github.com/diogoncalves1/cashmanager-be/issues/128) | debt-invitations | `refactor/debt-invitations-pagination` |
| [#129](https://github.com/diogoncalves1/cashmanager-be/issues/129) | goals | `refactor/financial-goals-pagination` |
| [#130](https://github.com/diogoncalves1/cashmanager-be/issues/130) | goal-transactions | `refactor/goal-transactions-pagination` |
| [#131](https://github.com/diogoncalves1/cashmanager-be/issues/131) | goal-invitations | `refactor/goal-invitations-pagination` |
| [#132](https://github.com/diogoncalves1/cashmanager-be/issues/132) | recurring | `refactor/recurring-pagination` |
| [#133](https://github.com/diogoncalves1/cashmanager-be/issues/133) | friendships | `refactor/friendships-pagination` |
| [#134](https://github.com/diogoncalves1/cashmanager-be/issues/134) | friend-requests | `refactor/friend-requests-pagination` |
| [#135](https://github.com/diogoncalves1/cashmanager-be/issues/135) | users | `refactor/user-discovery-pagination` |
| [#136](https://github.com/diogoncalves1/cashmanager-be/issues/136) | shared-roles | `refactor/shared-roles-pagination` |
| [#137](https://github.com/diogoncalves1/cashmanager-be/issues/137) | category-options | `refactor/category-list-options` |
| [#138](https://github.com/diogoncalves1/cashmanager-be/issues/138) | currency-cache | `bugfix/currency-model-cache` |
| [#139](https://github.com/diogoncalves1/cashmanager-be/issues/139) | language-cache | `refactor/language-code-cache` |
| [#140](https://github.com/diogoncalves1/cashmanager-be/issues/140) | listing-docs | `docs/module-listing-architecture` |
| [#141](https://github.com/diogoncalves1/cashmanager-be/issues/141) | notifications | `refactor/notifications-pagination` |

Frontend: [cashmanager-web#251](https://github.com/diogoncalves1/cashmanager-web/issues/251), branch `refactor/module-listing-api`. Os testes de domínio do backend ficam em `Modules/<Module>/Tests`, com suites registadas em phpunit.xml.

## Dependências e revisão

As branches/PRs são encadeados para separar os diffs por módulo/subcategoria. A base local inicial do Back é `feature/CM-120` (f225622); a do Front é `feature/CM-250` (fa1b096). Essas bases contêm trabalho anterior de reservas e não são integradas por esta tarefa. Os PRs identificam a branch base e a ordem de revisão. Publicação de branches serve apenas para abrir os PRs; não autoriza integração.

## Validação e entrega

O registo final dos comandos e PRs será acrescentado após as verificações. Não há nova migration nesta refatoração. Deploy BE/FE coordenado; consumidores Flutter/outros ainda têm de adotar o contrato antes da entrega. SQLite não comprova execução das expressões/views MySQL em produção.

