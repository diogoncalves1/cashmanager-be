# CashManager — documentação do backend

Ponto de entrada para desenvolver, integrar e operar o backend Laravel em `Back/CashManager`.

**Revisão: 20 de novembro de 2026.** A referência é o código local, incluindo alterações ainda não commitadas. O inventário de rotas foi extraído da aplicação com `php artisan route:list --json`. Isto confirma registo de rotas, não o sucesso de cada pedido nem o estado do servidor de produção.

## Começar pelo objetivo

| Objetivo | Percurso de leitura |
|---|---|
| Preparar o ambiente | [Instalação local](getting-started/local-setup.md) → [Configuração](getting-started/configuration.md) |
| Perceber o projeto | [Visão geral](architecture/overview.md) → [Camadas](architecture/layers-and-modules.md) → [Mapa dos módulos](architecture/module-map.md) |
| Integrar frontend ou mobile | [Convenções da API](api/README.md) → contrato do domínio → [inventário completo de rotas](api/route-inventory.md) |
| Alterar saldos, dívidas ou metas | [Fluxos financeiros](architecture/financial-flows.md) → módulo → [base de dados](architecture/database/tables-and-views.md) |
| Alterar acessos ou partilha | [Autenticação e permissões](architecture/auth-and-permissions.md) → [códigos de permissões](architecture/database/permissions.md) → [API de partilha](api/sharing.md) |
| Publicar ou diagnosticar | [Operação e deploy](operations/deployment.md) → [Diagnóstico](operations/troubleshooting.md) |
| Validar uma alteração | [Guia de desenvolvimento](process/development-guide.md) → [Testes](process/testing.md) |
| Preparar trabalho novo | [Intenções](intents/README.md) → [Especificações](specs/README.md) → [Planos](plans/README.md) → [Git](process/git-workflow.md) |
| Atualizar estas docs | [Manutenção e fontes](process/documentation.md) |

## Arquitetura e regras de negócio

- [Paginação, filtros e pesquisa por entidade](architecture/module-listings.md).

- [Visão geral](architecture/overview.md): produto, stack e limites dos repositórios.
- [Camadas e módulos](architecture/layers-and-modules.md): estrutura e percurso de um pedido.
- [Mapa dos 18 módulos](architecture/module-map.md): responsabilidades e dependências.
- [Fluxos financeiros](architecture/financial-flows.md): transações, pagamentos, reservas e recorrências.
- [Autenticação, permissões e partilha](architecture/auth-and-permissions.md).
- [Serviços transversais](architecture/cross-cutting-services.md): atividade, notificações, emails, idiomas, câmbios e tarefas.
- [Tabelas e views](architecture/database/tables-and-views.md) e [permissões](architecture/database/permissions.md).
- [Problemas conhecidos](architecture/known-issues.md): inclui diferenças introduzidas pela refatoração local.

| Domínio | Regras e implementação | Contrato HTTP |
|---|---|---|
| Contas e transações | [Accounts](architecture/modules/accounts.md) | [Contas](api/accounts.md), [transações](api/transactions.md) |
| Dívidas e pagamentos | [Debts](architecture/modules/debts.md) | [Dívidas](api/debts.md) |
| Metas e reservas | [FinancialGoal](architecture/modules/financial-goal.md) | [Metas](api/financial-goals.md) |
| Recorrências | [RecurringTransaction](architecture/modules/recurring-transaction.md) | [Recorrentes](api/recurring-transactions.md) |
| Categorias, moedas e dashboard | [Catálogo e dashboard](architecture/modules/category-currency-dashboard.md) | [Catálogo](api/catalog.md), [dashboard](api/dashboard.md) |
| Utilizadores, amigos e notificações | [User, Friends, Notification](architecture/modules/user-friends-notification.md) | [Autenticação](api/auth.md), [utilizadores](api/users.md), [amigos](api/friends.md), [notificações](api/notifications.md) |
| Serviços de suporte | [Suporte](architecture/modules/support.md) | [Partilha](api/sharing.md) |

## Estado e limites

- As listagens paginadas da API usam [ModulePaginate, ModuleFilter e ModuleSearch](architecture/module-listings.md). DataTables ficam no backoffice.
- Policies, repositories e flags `actions` ainda não estão totalmente alinhados. Consultar os problemas conhecidos antes de alterar permissões.
- Budgets é planeamento no frontend; não é um módulo implementado neste backend.
- As docs estão em `docs/`, dentro do repositório Git do backend, e podem ser versionadas com o código; ver [manutenção](process/documentation.md).
- O registo desta revisão e os limites da verificação estão em [auditoria da documentação](process/documentation-audit.md).

As notas compactas para agentes estão em [AGENTS.md](../AGENTS.md). Especificações técnicas mantidas junto do código são ligadas a partir do [índice de specs](specs/README.md).
