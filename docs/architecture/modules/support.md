# Módulos de suporte: ApiResponder, ActivityLog, Permission, SharedRoles, Language, Asset

## ApiResponder
Trait `Traits/RespondsWithApi.php` (`ok`, `fail`, `respondPaginated`) + `Services/ApiResponse.php`. Sem rotas nem tabelas. Formato em [layers-and-modules](../layers-and-modules.md#formato-das-respostas-modulesapiresponder).

## ActivityLog
Tabela `activity_logs`; `Enums/ActivityTypeEnum` (`account`, `debt`, `financial_goal`); `ActivityLogRepository::storeActivity/destroyByType`; `ActivityLogPaginate`; `Core/ActivityLogMessageResolver`. As rotas do próprio módulo estão comentadas — a leitura é feita pelos endpoints `/{recurso}/{id}/activity`. Detalhes em [cross-cutting-services](../cross-cutting-services.md#activity-log-modulesactivitylog).

## Permission (RBAC global / admin)
Tabelas `roles`, `permissions(name, code, category, visible)`, `role_permissions`, `role_user`. Gate `authorization` em `PermissionServiceProvider`. Só rotas web: `/admin/roles` (+ `roles/manage/{id}`), `/admin/permissions` (+ `permissions/data`). Ver [auth-and-permissions](../auth-and-permissions.md).

## SharedRoles (RBAC por recurso)
Tabelas `shared_roles`, `shared_permissions`, `shared_permission_roles`. Trait `Traits/IsShareable.php`, serviço `CheckPermission`, exceções de convite/membros em `Exceptions/` (traduzidas).
- API: `GET /api/v1/shared-roles` (sanctum; todos exceto creator); `GET shared-roles/check-code` e `shared-permissions/check-code` (sessão admin).
- Admin: `/admin/shared-roles` (+ `{id}/manage`), `/admin/shared-permissions`.

## Language
Tabela `languages(code único, name JSON)`. `GET /api/v1/languages`; admin `/admin/languages`. `LanguageRepository::allCodes()` usado pelo `SetLocale` e pelo `CategoryRequest` do admin.

## Asset (catálogo de ativos/ações — experimental)
- Tabela `assets`: `ticker` único, nome, país, bolsa, setor, website, logo, JSONs (preço, fundamentals, dividendos, histórico, notícias), `price`, `currency`, `type`, timestamps de atualização. View `asset_search_view`.
- `AssetRepository::store` corre `python3 Modules/Asset/Scripts/finance_fetcher.py <ticker>` (yfinance).
- Rotas API **sem autenticação**: `GET v1/assets/search`, `GET v1/assets/{ticker}/show`, `GET v1/assets/{id}` (e `GET v1/asset` aponta para um método inexistente). Admin: `/admin/assets`.
- Não está integrado no resto do produto.
