# 01 — Visão geral

## O produto

CashManager é uma aplicação de gestão de finanças pessoais **com partilha entre amigos**: contas, transações, dívidas, metas de poupança e transações recorrentes podem ser partilhadas com outros utilizadores, cada um com um papel (shared role) que define o que pode fazer.

## Repositórios / pastas (`/Users/diogogoncalves/development/CashManager`)

| Pasta | O quê |
|---|---|
| `Back/CashManager` | **Este backend** — API REST Laravel (repo git próprio, `cashmanager-be`) |
| `Back/CashManager/docs` | Esta documentação |
| `Back/env`, `Back/htaccess` | Cópias de referência; não certificam o estado de produção. `env` pode conter segredos |
| `Back/CashManager.zip` | Snapshot/backup do projeto |
| `Front/CashManager` | Frontend Next.js (TypeScript, Tailwind) — consome esta API |
| `App/cashmanager` | App Flutter (mobile/desktop) |
| `Relatorio`, `Apresentacao` | Relatório e apresentação académica do projeto |

## Stack

- PHP 8.2, **Laravel 12**
- **nwidart/laravel-modules 12** — todo o domínio está em `Modules/` (namespace `Modules\`)
- **Laravel Sanctum 4** — autenticação por token Bearer na API
- **Yajra DataTables 12** — listagens do backoffice; API usa ModulePaginate
- Laravel Telescope (debug), Predis (Redis)
- Blade + Vite 7 + Tailwind 4 — apenas para o backoffice admin (`/admin`)
- MySQL 8 (produção/local); SQLite em memória nos testes
- Python 3 (`yfinance`, `BeautifulSoup`) só para o módulo `Asset` (`Modules/Asset/Scripts/finance_fetcher.py`)
- API externa de câmbios: `v6.exchangerate-api.com` (chave `CURRENCY_API_KEY`)

## Preparar e operar

- [Instalação local](../getting-started/local-setup.md): requisitos, MySQL, dependências, dados iniciais e execução.
- [Configuração](../getting-started/configuration.md): variáveis e drivers, sem valores reais de ambientes.
- [Deploy e operação](../operations/deployment.md): cron único, filas `default`/`emails`, caches e recuperação.
- [Diagnóstico](../operations/troubleshooting.md): sintomas e verificações por componente.
- [Testes](../process/testing.md): suites, cobertura funcional e limites do SQLite.

Executar os comandos na raiz `Back/CashManager`. A aplicação usa UTC no código; câmbios têm agendamento explícito Europe/Lisbon. A configuração real de produção precisa de confirmação no servidor.

As migrations de reservas das metas são forward-only e têm roteiro próprio: [PRODUCTION-ROLLOUT.md](../../Modules/FinancialGoal/PRODUCTION-ROLLOUT.md).

## Onde ler a seguir

- Camadas e módulos: [layers-and-modules.md](layers-and-modules.md)
- Regras e detalhes de cada módulo: [modules/](modules/)
- Base de dados: [database/](database/)
- API: [../api/README.md](../api/README.md)
- Processo de trabalho: [../process/](../process/)
