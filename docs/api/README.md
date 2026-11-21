# API — convenções gerais

Referência da API REST consumida pelo frontend Next.js e pela app Flutter. Cada domínio tem o seu ficheiro, listado no fim.

## Base

| | |
|---|---|
| **Base URL** | `{APP_URL}/api/v1` |
| **Formato** | JSON (`Accept: application/json`, `Content-Type: application/json`) |
| **Autenticação** | `Authorization: Bearer <token>` (Laravel Sanctum, token devolvido por `POST /login`) |
| **Idioma** | Vem da preferência do utilizador (`user_preferences.lang`, `pt` ou `en`), aplicada pelo middleware `setlocale`. Mensagens e nomes traduzidos seguem esse idioma |
| **Datas** | `YYYY-MM-DD` |
| **Montantes** | Numéricos com 2 casas decimais. Nas listagens existem também campos `*Formated` (com símbolo da moeda) |

Os endpoints sem a indicação "público" exigem token. Nos erros de autenticação renderizados pelo handler global, a resposta inclui `success: false`, `data: null` e `message` (401). Ver as variantes de erro abaixo.

## Envelope de resposta

Respostas normais (`ok()` / `fail()`):

```json
{
  "success": true,
  "message": "Conta \"Ordenado\" criada com sucesso.",
  "data": { "id": 1, "name": "Ordenado" },
  "errors": null,
  "meta": { }
}
```

- `message` vem traduzida. O frontend pode mostrá-la diretamente num toast.
- `meta` só aparece quando o endpoint junta dados extra (gráficos, resumos, contagens).
- Em caso de erro: `success: false`, `data: null`, `errors` com o detalhe (por vezes a exceção serializada).

## Variantes de erro

Em `bootstrap/app.php`, erros JSON renderizados pelo Laravel para o prefixo da API são reformatados para `{success:false, message, data:null}` e incluem `errors` apenas se existir no corpo original. O status HTTP é preservado. Exemplo de validação:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": null,
  "errors": { "per_page": ["..."] }
}
```

Uma resposta devolvida diretamente por um controller pode ter outro formato. `fail()` continua a usar o envelope ApiResponder. As `HttpResponseException` dos FormRequests também passam pelo handler: o JSON de validação das recorrentes recebe `data:null`. Não confundir resposta devolvida normalmente pelo controller com uma resposta transportada por uma exceção.

## Códigos de estado

| Código | Quando |
|---|---|
| 200 | Sucesso |
| 201 | `POST /login` |
| 401 | Sem token ou token inválido. **Também** usado por algumas exceções de permissão de SharedRoles (`Unauthorized*`, `InviteUserNotAllowed`) |
| 403 | Sem permissão sobre o recurso (shared role) ou email por verificar no login |
| 404 | Recurso inexistente (exceções `*NotFoundException`) |
| 422 | Erro de validação. Campos `message` e `errors`, com `success:false`/`data:null` quando passa pelo handler global |
| 500 | Erro inesperado. **Na prática também aparece em muitos erros previsíveis**: `ModelNotFoundException`/`firstOrFail` apanhados pelo `catch` genérico, exceções de negócio com código 500, membros sem shared role. Cada ficheiro de domínio indica o código real. Ver [known-issues](../architecture/known-issues.md) |

## Listagens paginadas

Todas as listagens paginadas da API usam `ModulePaginate` + `ModuleFilter` + `ModuleSearch` e Resources. As DataTables Yajra ficam no backoffice.

```json
{"success":true,"message":"","data":[],"pagination":{"current_page":1,"per_page":10,"total":0,"last_page":1},"errors":null,"meta":{}}
```

Enviar `page` (≥ 1), `per_page` (1–100), `order_by` (whitelist), `sort_by` (`asc`/`desc`) e `search` (texto literal, máximo 200). Campos de ordenação/direção inválidos e o protocolo antigo (`start`, `length`, `columns`, `order`, `draw`, `search[value]`) dão 422. `pagination.total` é o total visível após filtros/pesquisa; os extras ficam em `meta`.

Filtros usam `campo[operador]=valor`; cada classe declara campos, operadores e validação. Escalares são aceites como `eq` quando permitido, e aliases camelCase estão documentados no domínio. Valores zero são preservados e intervalos combinam `gte` e `lte`. Pickers/feed e endpoints auxiliares sem paginação conservam o contrato próprio.

Consultar [arquitetura, campos e exemplos de ModulePaginate](../architecture/module-listings.md) para as regras completas de filtros, pesquisa, contagens, whitelists e migração do frontend.

## Recursos partilhados

Contas, dívidas e metas têm membros e convites com os mesmos endpoints: ver [sharing.md](sharing.md). As permissões de cada ação estão em [../architecture/database/permissions.md](../architecture/database/permissions.md).

## Inventário completo

[Rotas registadas da API, backoffice e pacotes](route-inventory.md), extraídas da aplicação. A presença de uma rota não garante que o handler exista ou execute com sucesso.

## Índice de endpoints por domínio

| Ficheiro | Prefixos |
|---|---|
| [auth.md](auth.md) | `register`, `login`, `logout`, `me`, `verify-email`, `reset-password`, `change-password`, `reset-change-password` |
| [users.md](users.md) | `me` (PUT), `users/search`, `check-username`, `user-preferences` |
| [accounts.md](accounts.md) | `accounts`, `account/resume` |
| [transactions.md](transactions.md) | `transactions` |
| [debts.md](debts.md) | `debts`, `debt-payments` |
| [financial-goals.md](financial-goals.md) | `financial-goals`, `financial-goal-transactions` |
| [recurring-transactions.md](recurring-transactions.md) | `recurrings` |
| [dashboard.md](dashboard.md) | `dashboard-overview` |
| [sharing.md](sharing.md) | convites e membros de `accounts`, `debts` e `financial-goals` |
| [friends.md](friends.md) | `friendships`, `friendship-requests` |
| [notifications.md](notifications.md) | `notifications` |
| [catalog.md](catalog.md) | `categories`, `currencies`, `languages`, `shared-roles`, `assets` |

As rotas web do backoffice (`/admin/...`) não fazem parte desta API e estão descritas em [../architecture/modules/](../architecture/modules/).
