# Módulos Category, Currency, DashboardCharts

## Category (`Modules/Category`)

Categorias **por defeito** (geridas no admin) + até **50 categorias pessoais** por utilizador. Especificação completa: `Modules/Category/README.md`.

- Tabela `categories`: `name` (JSON traduções), `type` (`revenue|expense`), `icon`, `color`, `code`, `is_default`, `parent_id`, `user_id`, `archived_at`.
- `Entities/Category.php`: `PERSONAL_LIMIT = 50`; `INTERNAL_CODES = ['financialGoalContribution','financialGoalWithdrawal']`; scopes `visibleTo` (defeito OU do próprio) e `selectable` (visível, não arquivada, não interna).
- `Services/PersonalCategoryService.php` (store/update/archive/destroy):
  - lock na linha do user (serializa escritas); a quota de 50 **inclui arquivadas**;
  - nomes duplicados verificados sem distinção de maiúsculas/espaços contra as próprias e todas as traduções das por defeito;
  - só apaga sem transações nem recorrentes (incluindo soft-deleted); caso contrário 422 `category::validation.in_use` — o cliente deve arquivar;
  - categorias por defeito ou de outros → 403.
- `Rules/SelectableCategory.php` (+ trait `Http/Requests/ValidatesCategorySelection.php`): tipo coerente e selecionável; em edição aceita a categoria original mesmo arquivada/de outro membro; `lockAndValidate` revalida dentro da transação.
- `Support/CategoryIcons.php::ALL`: 112 ícones — **tem de coincidir** com o catálogo e `iconMap` do frontend (inclui legado `DollarSign`).
- Rotas API (detalhe em [../../api/catalog.md](../../api/catalog.md)): `apiResource categories` + `PATCH categories/{id}/archive` (`Api\CategoryController`). Admin: `/admin/categories`.
- Teste: `php artisan test --filter=PersonalCategoriesTest`.

## Currency (`Modules/Currency`)

- Tabela `currencies`: `code` (3), `symbol`, `name` (JSON), `rate` decimal(15,4) = unidades por 1 USD.
- Comando `currency:fetch-daily` (`Console/Commands/FetchApiCurrenciesDaily.php`): `GET https://v6.exchangerate-api.com/v6/{CURRENCY_API_KEY}/latest/USD`, atualiza `rate` por código; erros só são registados. Agendado diariamente às 01:00 Europe/Lisbon (ver [cross-cutting-services](../cross-cutting-services.md)).
- `Core/Helpers::convertCurrency($amount, $from, $to)` = `amount * to.rate / from.rate`.
- Rotas:
  - `GET /api/v1/currencies`, `GET /api/v1/currencies/{id}` (sanctum + setlocale).
  - Escrita (`POST/PUT/DELETE currencies`, `GET currencies/check-code`, `GET currencies/update-rates` → corre o comando) com `auth` + `web` (sessão do backoffice).
  - Admin: `/admin/currencies`.

## DashboardCharts (`Modules/DashboardCharts`)

Sem tabelas. Uma rota: `GET /api/v1/dashboard-overview` (`auth:sanctum`, sem `setlocale`) → `DashboardChartsController@overview`.

- `Repositories/DashboardRepository::getOverviewData`: receitas/despesas convertidas para a moeda do utilizador + variação % vs mês anterior (`TransactionRepository::getUserConvertedSum`); séries `monthly`, `quarterly`, `annual`, `userTotal` (`TransactionRepository::getChartsData`).
- `Repositories/DashboardWidgetsRepository.php`:
  - `accounts`: `AccountRepository::getStats`;
  - `debts`: 3 pending por `dueDate`;
  - `goals`: 3 in_progress por progresso desc;
  - `recurring`: `RecurringSummaryRepository::forUser` (totais por moeda, upcoming ≤ 5).
- Regras: respeitar a visibilidade de cada domínio; nunca devolver listas completas de DataTable nem fazer HTTP interno.
- Resposta formatada por `Http/Resources/DashboardOverviewResource.php`.
