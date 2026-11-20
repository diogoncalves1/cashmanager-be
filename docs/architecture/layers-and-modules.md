# Arquitetura — visão geral e camadas

## Visão de alto nível

```
Frontend Next.js / App Flutter
        │  HTTPS, Authorization: Bearer <token Sanctum>
        ▼
  /api/v1/...  (routes/api.php + Modules/*/Routes/api.php)
        │  middleware: api → auth:sanctum → setlocale
        ▼
  Controller (extends App\Http\Controllers\ApiController)
        │  FormRequest valida (authorize() = true)
        ▼
  Repository / Service  ← regras de negócio, verificação de permissões,
        │                  DB::transaction + lockForUpdate, ActivityLog
        ▼
  Entities (Eloquent) + views SQL (*_view) ── MySQL
        │
        ▼
  API Resource / Collection  →  JSON {success, message, data, errors, meta}
```

Backoffice admin: rotas `web` em `/admin/...` (Blade, sessão, middleware `auth` + `admin`). Serve para gerir categorias por defeito, moedas, idiomas, utilizadores, roles e permissões globais, shared roles, tipos de notificação, broadcasts e layouts de email.

## Estrutura de pastas

```
app/                     código base partilhado
  Http/Controllers/ApiController.php   base de todos os controllers API (ok/fail/safe)
  Http/Controllers/AppController.php   allowedAction($perm) → Gate 'authorization'
  Http/Controllers/Api/V1/AuthController.php  register/login/logout/me/passwords/verify
  Http/Middleware/CheckAdmin.php       alias 'admin'
  Paginates/ModulePaginate.php
  Filters/Filter.php                   filtros por query string (?campo[op]=valor; op ∈ gt,gte,lt,lte,eq,ne,in)
  Repositories/                        interfaces Repository*Interface, FilesRepository (uploads)
app/Paginates/ModulePaginate.php    base das listagens da API
app/Filters/ModuleFilter.php        filtros validados
app/Searches/ModuleSearch.php       pesquisa textual agrupada
bootstrap/app.php        routing, alias 'admin', normalização de erros JSON
routes/api.php           auth em /api/v1
routes/web.php           /admin + rotas Breeze (auth.php)
Modules/<Nome>/          um módulo por domínio
modules_statuses.json    módulos ativos (todos a true)
tests/Feature, tests/Unit
```

## Anatomia de um módulo (`nwidart/laravel-modules`)

```
Modules/<Nome>/
  Config/config.php
  Console/Commands/        comandos artisan
  Core/                    helpers (ex. Currency/Core/Helpers, ActivityLog/Core/ActivityLogMessageResolver)
  DataTables/              classes Yajra apenas para backoffice
  Database/Migrations|Seeders|Factories
  Entities/                modelos Eloquent + modelos read-only sobre views SQL (*View)
  Enums/ Events/ Listeners/ Observers/ Jobs/ Mail/ Notifications/
  Exceptions/              exceções de domínio; $code = status HTTP
  Filters/ Paginates/ Searches/   pipeline de listagem por entidade
  Http/Controllers/Api[/V1]/   controllers API
  Http/Controllers/(Admin/)    controllers do backoffice
  Http/Requests/ Http/Resources/ Http/Middleware/
  Policies/
  Providers/<Nome>ServiceProvider.php, RouteServiceProvider.php, EventServiceProvider.php
  Repositories/            lógica de negócio e queries
  Services/ Actions/ Rules/ Traits/ Support/
  Resources/lang/{pt,en}/  traduções (namespace "<nome>::")
  Resources/views/         Blade do backoffice
  Routes/api.php, web.php
  Tests/
```

O `RouteServiceProvider` de cada módulo carrega `Routes/api.php` com `middleware('api')->prefix('api')->name('api.')` e `Routes/web.php` com `middleware('web')`. A maioria dos domínios usa `v1` com `auth:sanctum` e `setlocale`, mas existem exceções públicas e por sessão no catálogo. Ver [inventário real](../api/route-inventory.md). O prefixo base das rotas centrais vem de `config('app.api_prefix')`; vários providers de módulos ainda o definem diretamente.

## Convenções do fluxo de um pedido

1. **Controller**: delega no repository dentro de `try/catch(\Exception)`. Devolve `$this->ok(Resource, __('modulo::messages...'))` ou `$this->fail($e->getMessage(), $e, $e->getCode())`. Alternativa: `$this->safe(fn() => ...)`, que relança `ValidationException`, apanha outros `Throwable`, regista o erro e devolve `fail`.
2. **FormRequest**: só valida (`authorize()` devolve `true`). A autorização fica nas policies/Gates chamados ou nas verificações do repository; há implementações distintas por domínio.
3. **Repository**, ou `Services/` nos domínios complexos (`GoalReservations`, `PersonalCategoryService`, `RecurringTransactionGenerator`):
   - obtém o shared role do utilizador sobre o recurso e verifica a permissão;
   - corre dentro de `DB::transaction` com `lockForUpdate` (ordem habitual: user → account → recurso);
   - atualiza as colunas com totais guardados (saldos, `paid_amount`, `contributed_amount`);
   - escreve `ActivityLogRepository::storeActivity(...)`.
4. **Resource/Collection**: formata a resposta. Os dados extra vão em `additionals`, que acaba em `meta`.
5. **Listagens**: controller → ModulePaginate → ModuleFilter/ModuleSearch → paginator Eloquent → Resource → respondPaginated. Consultar [regras e exemplos](module-listings.md).

## Formato das respostas (`Modules/ApiResponder`)

```json
{ "success": true,  "message": "…", "data": {…}, "errors": null, "meta": {…opcional} }
{ "success": false, "message": "…", "data": null, "errors": <exceção|array>, "meta": … }
```

- `ok($data, $message='', $code=200, $additionals=null)` / `fail($message, $errors=null, $code=400, $additionals=null)`.
- O status HTTP vem do `$code` da exceção. Fora de 100–511 passa a 500 (fail) ou 200 (ok). Por isso cada exceção de domínio define `$code` (404, 403, 422…).
- `respondPaginated`: `{success, message, data, pagination, errors}` e `meta` opcional; ModulePaginate valida parâmetros e aplica ordenação estável (tamanho 10, máximo 100).
- Validação relançada: o handler de `bootstrap/app.php` mantém 422 e acrescenta `success:false`/`data:null` ao JSON de erro do Laravel na API. Respostas devolvidas diretamente pelo controller podem ter outro formato; as HttpResponseException dos FormRequests passam pelo handler e recebem a normalização.
- Exceções a este formato: o `login` devolve `{user, token}` com 201.

## Dependências entre módulos

```
RecurringTransaction ──► Accounts (TransactionRepository) / Debts (DebtPaymentRepository) / FinancialGoal (FinancialGoalTransactionRepository)
Debts (DebtPayment)  ──► Accounts (TransactionRepository) — cada pagamento cria uma despesa
FinancialGoal        ──► Accounts (reservas sobre contas; "usage" liga ou cria uma despesa)
DashboardCharts      ──► Accounts, Debts, FinancialGoal, RecurringTransaction (via repositories, nunca HTTP interno)
Accounts/Debts/Goals ──► SharedRoles (IsShareable), Friends (convites só entre amigos), ActivityLog
Category             ──► usado por Accounts e RecurringTransaction (regra SelectableCategory)
UserPreferences      ──► setlocale, moeda preferida (conversões)
User / Friends       ──► Notification, EmailLayout
```

Ver também: [auth-and-permissions.md](auth-and-permissions.md) e [cross-cutting-services.md](cross-cutting-services.md).
