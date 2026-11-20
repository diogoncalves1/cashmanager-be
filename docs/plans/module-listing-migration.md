# Migração modular das listagens

Pedido de 20/11/2026. Nova arquitetura em [ModulePaginate, ModuleFilter e ModuleSearch](../architecture/module-listings.md).

Todas as issues pertencem ao projeto [Cash Manager](https://github.com/users/diogoncalves1/projects/2), campo Version = Version 1.6.2 para esta entrega. Ao abrir cada PR, a issue associada passa para Code Complete no projeto. PRs aguardam confirmação explícita do responsável; não integrar nem ativar auto-merge.

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

Não há nova migration nesta refatoração. Deploy BE/FE coordenado; consumidores Flutter/outros ainda têm de adotar o contrato antes da entrega. SQLite não comprova execução das expressões/views MySQL em produção.

## Pull requests abertos, por ordem de dependência

| Ordem | Âmbito | Issue | PR | Base do PR | Commit |
|---|---|---|---|---|---|
| 1 | listing-core | [#121](https://github.com/diogoncalves1/cashmanager-be/issues/121) | [#142](https://github.com/diogoncalves1/cashmanager-be/pull/142) | `feature/CM-120` | `7fd0baf2` |
| 2 | shared-roles | [#136](https://github.com/diogoncalves1/cashmanager-be/issues/136) | [#143](https://github.com/diogoncalves1/cashmanager-be/pull/143) | `refactor/api-listing-core` | `d64f87c9` |
| 3 | activity | [#125](https://github.com/diogoncalves1/cashmanager-be/issues/125) | [#144](https://github.com/diogoncalves1/cashmanager-be/pull/144) | `refactor/shared-roles-pagination` | `918b2a32` |
| 4 | accounts | [#122](https://github.com/diogoncalves1/cashmanager-be/issues/122) | [#145](https://github.com/diogoncalves1/cashmanager-be/pull/145) | `refactor/activity-pagination` | `142f9dc2` |
| 5 | transactions | [#123](https://github.com/diogoncalves1/cashmanager-be/issues/123) | [#146](https://github.com/diogoncalves1/cashmanager-be/pull/146) | `refactor/accounts-pagination` | `8f358859` |
| 6 | account-invitations | [#124](https://github.com/diogoncalves1/cashmanager-be/issues/124) | [#147](https://github.com/diogoncalves1/cashmanager-be/pull/147) | `refactor/transactions-pagination` | `d9790737` |
| 7 | debts | [#126](https://github.com/diogoncalves1/cashmanager-be/issues/126) | [#148](https://github.com/diogoncalves1/cashmanager-be/pull/148) | `refactor/account-invitations-pagination` | `d1046f2c` |
| 8 | debt-payments | [#127](https://github.com/diogoncalves1/cashmanager-be/issues/127) | [#149](https://github.com/diogoncalves1/cashmanager-be/pull/149) | `refactor/debts-pagination` | `3c0dc338` |
| 9 | debt-invitations | [#128](https://github.com/diogoncalves1/cashmanager-be/issues/128) | [#150](https://github.com/diogoncalves1/cashmanager-be/pull/150) | `refactor/debt-payments-pagination` | `f48535a9` |
| 10 | goals | [#129](https://github.com/diogoncalves1/cashmanager-be/issues/129) | [#151](https://github.com/diogoncalves1/cashmanager-be/pull/151) | `refactor/debt-invitations-pagination` | `3906ab54` |
| 11 | goal-transactions | [#130](https://github.com/diogoncalves1/cashmanager-be/issues/130) | [#152](https://github.com/diogoncalves1/cashmanager-be/pull/152) | `refactor/financial-goals-pagination` | `be165de3` |
| 12 | goal-invitations | [#131](https://github.com/diogoncalves1/cashmanager-be/issues/131) | [#153](https://github.com/diogoncalves1/cashmanager-be/pull/153) | `refactor/goal-transactions-pagination` | `e0dcbbad` |
| 13 | recurring | [#132](https://github.com/diogoncalves1/cashmanager-be/issues/132) | [#154](https://github.com/diogoncalves1/cashmanager-be/pull/154) | `refactor/goal-invitations-pagination` | `34d126de` |
| 14 | friendships | [#133](https://github.com/diogoncalves1/cashmanager-be/issues/133) | [#155](https://github.com/diogoncalves1/cashmanager-be/pull/155) | `refactor/recurring-pagination` | `978cf241` |
| 15 | friend-requests | [#134](https://github.com/diogoncalves1/cashmanager-be/issues/134) | [#156](https://github.com/diogoncalves1/cashmanager-be/pull/156) | `refactor/friendships-pagination` | `987ac312` |
| 16 | users | [#135](https://github.com/diogoncalves1/cashmanager-be/issues/135) | [#157](https://github.com/diogoncalves1/cashmanager-be/pull/157) | `refactor/friend-requests-pagination` | `c3310fe9` |
| 17 | category-options | [#137](https://github.com/diogoncalves1/cashmanager-be/issues/137) | [#158](https://github.com/diogoncalves1/cashmanager-be/pull/158) | `refactor/user-discovery-pagination` | `45b6838a` |
| 18 | notifications | [#141](https://github.com/diogoncalves1/cashmanager-be/issues/141) | [#159](https://github.com/diogoncalves1/cashmanager-be/pull/159) | `refactor/category-list-options` | `8a616224` |
| 19 | currency-cache | [#138](https://github.com/diogoncalves1/cashmanager-be/issues/138) | [#160](https://github.com/diogoncalves1/cashmanager-be/pull/160) | `refactor/notifications-pagination` | `4dc2b622` |
| 20 | language-cache | [#139](https://github.com/diogoncalves1/cashmanager-be/issues/139) | [#161](https://github.com/diogoncalves1/cashmanager-be/pull/161) | `bugfix/currency-model-cache` | `19cb5671` |
| 21 | listing-docs | [#140](https://github.com/diogoncalves1/cashmanager-be/issues/140) | [#162](https://github.com/diogoncalves1/cashmanager-be/pull/162) | `refactor/language-code-cache` | `87781467` |

Frontend: [PR #252](https://github.com/diogoncalves1/cashmanager-web/pull/252), commits `25fd061` e `8cea415`, base `feature/CM-250`.

A sequência está publicada apenas para revisão. As 22 issues associadas aos PRs foram movidas para Code Complete no campo Status do projeto e verificadas na UI, sem merge nem fecho. Esta passa a ser a regra de abertura de PRs. Nenhum merge, auto-merge ou deploy foi feito. A associação das 22 issues ao projeto e à iteração Version 1.6.2 foi verificada na vista V1.6.2 do GitHub Projects.

## Resultados executados

- `composer test`: **112 testes passaram, 3145 asserções**, SQLite em memória; testes de domínio em Modules/<Module>/Tests.
- `vendor/bin/pint` nos PHP alterados: concluído. `git diff --check`: sem erros.
- `php artisan route:list --json` + script de inventário: 298 rotas, 141 API; inclui os endpoints administrativos paginados de SharedRoles, preservando o picker original.
- Front: `npm run typecheck` e `npm run build` passaram. O primeiro build ficou impedido pelo download de Google Fonts na sandbox; repetido com rede e concluído após corrigir imports sem uso.
- Front: `node --test tests/module-listing.test.mjs tests/recurring-requests.test.mjs tests/recurring-feedback.test.mjs tests/categories.test.mjs tests/goal-reservations.test.mjs tests/notification-listing.test.mjs`: **46 testes passaram**.
- Ligações Markdown locais verificadas por `python3 docs/scripts/check_links.py`. Âncoras e URLs remotos não são verificadas pelo script.

Estes resultados descrevem a árvore final cumulativa. Cada PR tem um diff separado; validar o seu módulo pelo caminho direto quando revisto isoladamente. As suites de descoberta automática dos módulos são registadas no PR final de docs/configuração. Não foi ensaiada a SQL MySQL desta refatoração nem testada a app Flutter.

A paginação do frontend também distingue as origens personal/broadcast ao deduplicar notificações com o mesmo ID; a leitura de uma mensagem pessoal não altera um broadcast com ID igual.
