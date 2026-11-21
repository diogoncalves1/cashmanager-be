# API — Catálogos

Dados de referência e catálogos: categorias (por defeito e pessoais), moedas, idiomas, roles partilhados e ativos financeiros. Convenções gerais (envelope, códigos de estado): ver [README.md](README.md).

Só as rotas `/api/v1` estão aqui. O backoffice (`/admin/...`) está em [../architecture/modules/](../architecture/modules/).

---

## Categorias (`Modules/Category`)

Categorias **por defeito** (`is_default = true`, geridas no backoffice, nome traduzido) e **pessoais** do utilizador (máx. 50 no total, contando as arquivadas e ambos os tipos). Especificação: `Modules/Category/README.md`. Arquitetura: [../architecture/modules/category-currency-dashboard.md](../architecture/modules/category-currency-dashboard.md#category-modulescategory).

Todas as rotas: `auth:sanctum` + `setlocale`. Código: `Http/Controllers/Api/CategoryController.php` → `Services/PersonalCategoryService.php`.

**Objeto `category` (`CategoryResource`):**

```json
{
  "id": "42",
  "name": "Supermercado semanal",
  "typeTranslated": "Despesa",
  "type": "expense",
  "icon": "ShoppingBasket",
  "color": "#15803d",
  "isDefault": false,
  "isArchived": false,
  "canManage": true,
  "canDelete": true
}
```

| Campo | Descrição |
|---|---|
| `id` | String |
| `name` | Nome no idioma do pedido. As pessoais têm um só nome; nas por defeito usa a tradução do locale, com fallback para `en` |
| `type` / `typeTranslated` | `expense` \| `revenue` e o rótulo traduzido |
| `icon` | Um de `Support/CategoryIcons::ALL` (112 ícones, alinhados com o `iconMap` do frontend) ou `null` |
| `color` | `#RRGGBB` ou `null` |
| `isDefault`, `isArchived` | Flags |
| `canManage` | `true` se a categoria é pessoal e do utilizador |
| `canDelete` | `canManage` e sem transações nem recorrentes (incluindo soft-deleted) |

O dono (`user_id`) nunca é exposto.

**Erros comuns a todas as mutações:**
- `422` de **validação do formulário** (`CategoryGuestRequest`) com formato próprio: `{"success": false, "message": "Verifica os campos da categoria.", "errors": {"name": ["Indica o nome da categoria."]}}`.
- `422` de **regras de negócio** (quota, duplicado, em uso) com o formato Laravel: `{"message": "...", "errors": {"name": ["..."]}}`.
- `403` — categoria por defeito ou de outro utilizador (`AuthorizationException`, `{"message": "This action is unauthorized."}`).
- `404` — id inexistente (formato Laravel).

### `GET /api/v1/categories`

Lista todas as categorias visíveis (por defeito + pessoais do utilizador), sem as internas das metas (`financialGoalContribution`, `financialGoalWithdrawal`). Ordena primeiro as por defeito e depois por `id`. Sem paginação.

**Autenticação/Permissão:** token.

**Parâmetros de query:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `type` | `revenue` \| `expense` | não | Filtra por tipo |
| `includeArchived` | boolean (`1`/`0`, `true`/`false`) | não | Inclui as arquivadas. Por defeito são excluídas |

**Resposta 200** (envelope sem `message` nem `errors`):

```json
{
  "success": true,
  "data": [
    {
      "id": "3",
      "name": "Alimentação",
      "typeTranslated": "Despesa",
      "type": "expense",
      "icon": "Utensils",
      "color": "#f97316",
      "isDefault": true,
      "isArchived": false,
      "canManage": false,
      "canDelete": false
    },
    {
      "id": "42",
      "name": "Supermercado semanal",
      "typeTranslated": "Despesa",
      "type": "expense",
      "icon": "ShoppingBasket",
      "color": "#15803d",
      "isDefault": false,
      "isArchived": false,
      "canManage": true,
      "canDelete": true
    }
  ],
  "meta": { "personalCount": 1, "limit": 50, "remaining": 49 }
}
```

`meta` conta **todas** as categorias pessoais do utilizador, independentemente dos filtros `type` e `includeArchived`.

**Erros:** `401`; `422` (formato Laravel) se `type` ou `includeArchived` forem inválidos.

---

### `POST /api/v1/categories`

Cria uma categoria pessoal.

**Autenticação/Permissão:** token.

**Body** (`CategoryGuestRequest`; antes de validar, o `name` é trimmed e os espaços seguidos ficam reduzidos a um):

| Campo | Regras | Descrição |
|---|---|---|
| `type` | `required\|in:revenue,expense` | Tipo. Imutável depois de criada |
| `name` | `required\|string\|max:100` | Único (sem distinção de maiúsculas/espaços) entre as pessoais do utilizador (incluindo arquivadas) e todas as traduções das por defeito |
| `icon` | `nullable\|string\|in:CategoryIcons::ALL` | Ícone |
| `color` | `nullable\|string\|regex:/^#[0-9a-fA-F]{6}$/` | Cor hex |
| `user_id`, `is_default`, `code`, `parent_id`, `archived_at` | `prohibited` | Não podem ser enviados |

```json
{ "name": "Supermercado semanal", "type": "expense", "icon": "ShoppingBasket", "color": "#15803d" }
```

**Resposta 201:**

```json
{
  "success": true,
  "message": "Categoria Supermercado semanal adicionada com sucesso!",
  "data": {
    "id": "42",
    "name": "Supermercado semanal",
    "typeTranslated": "Despesa",
    "type": "expense",
    "icon": "ShoppingBasket",
    "color": "#15803d",
    "isDefault": false,
    "isArchived": false,
    "canManage": true,
    "canDelete": true
  },
  "errors": null
}
```

**Erros:**
- `422` (formulário) — campos inválidos ou proibidos.
- `422` (Laravel) `errors.name` — "Podes criar até 50 categorias pessoais, incluindo as arquivadas." ou "Já existe uma categoria com este nome.".

---

### `GET /api/v1/categories/{id}`

Detalhe de uma categoria por defeito ou pessoal do utilizador. Inclui arquivadas e internas.

**Autenticação/Permissão:** token.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | Id da categoria |

**Resposta 200:** `{ "success": true, "message": "", "data": { …category }, "errors": null }`.

**Erros:** `404` — inexistente ou pessoal de outro utilizador.

---

### `PUT /api/v1/categories/{id}`

Edita o nome, ícone e cor de uma categoria pessoal do utilizador. A rota também aceita `PATCH`, mas o `name` é sempre obrigatório. As alterações aparecem também nos movimentos já existentes.

**Autenticação/Permissão:** token (só o dono).

**Parâmetros de rota:** `id` (int, obrigatório).

**Body** (`UpdateCategoryGuestRequest`): as mesmas regras do `POST`, mas `type` passa a `prohibited`. `icon` e `color` omitidos não mudam. Enviados com `null`, são limpos.

```json
{ "name": "Supermercado", "icon": "ShoppingCart", "color": "#16a34a" }
```

**Resposta 200:** `message: "Categoria Supermercado atualizada com sucesso!"`, `data` = objeto `category`.

**Erros:** `403` (por defeito ou de outro), `404`, `422` (formulário, incluindo `type` enviado; ou duplicado).

---

### `PATCH /api/v1/categories/{id}/archive`

Arquiva ou restaura uma categoria pessoal. As arquivadas deixam de estar disponíveis para novos movimentos, mas o histórico e a geração de recorrentes mantêm-se.

**Autenticação/Permissão:** token (só o dono).

**Parâmetros de rota:** `id` (int, obrigatório).

**Body:**

| Campo | Regras | Descrição |
|---|---|---|
| `is_archived` | `required\|boolean` | `true` arquiva (mantém a data original se já estiver arquivada), `false` restaura |

```json
{ "is_archived": true }
```

**Resposta 200:**

```json
{
  "success": true,
  "message": "Categoria arquivada. O histórico foi preservado.",
  "data": {
    "id": "42",
    "name": "Supermercado semanal",
    "typeTranslated": "Despesa",
    "type": "expense",
    "icon": "ShoppingBasket",
    "color": "#15803d",
    "isDefault": false,
    "isArchived": true,
    "canManage": true,
    "canDelete": false
  },
  "errors": null
}
```

Ao restaurar, `message: "Categoria restaurada."`.

**Erros:** `403`, `404`, `422` (formato Laravel) se `is_archived` faltar ou não for boolean.

---

### `DELETE /api/v1/categories/{id}`

Apaga definitivamente uma categoria pessoal sem transações nem recorrentes (incluindo recorrentes soft-deleted).

**Autenticação/Permissão:** token (só o dono).

**Parâmetros de rota:** `id` (int, obrigatório).

**Resposta 200:**

```json
{ "success": true, "message": "Categoria apagada.", "data": null, "errors": null }
```

**Erros:**
- `403`, `404`.
- `422` (Laravel) `errors.category` — "Esta categoria tem movimentos associados. Arquiva-a para preservar o histórico.". O cliente deve propor arquivar.

---

## Moedas (`Modules/Currency`)

Código: `Http/Controllers/Api/CurrencyController.php`, `Http/Resources/CurrencyResource.php`.

**Objeto `currency` (`CurrencyResource`):**

```json
{ "id": "1", "name": "Euro", "symbol": "€", "rate": 0.9234, "code": "EUR" }
```

- `name` vem no locale do pedido (com fallback para `en` se o locale não estiver em `languages`).
- `rate` = unidades da moeda por 1 USD, atualizado diariamente pelo comando `currency:fetch-daily`.

> Os GET exigem token Sanctum (não são públicos). Têm a ordem de middleware `['setlocale', 'auth:sanctum']`: o `setlocale` corre antes da autenticação e lança `Error('Não está logado')` (500) quando não consegue resolver o utilizador. Isso acontece sem token e possivelmente também com token, porque o guard por defeito ainda é `web` nesse momento. Ver [known-issues](../architecture/known-issues.md) #10. Convém confirmar e passar `auth:sanctum` para primeiro.

### `GET /api/v1/currencies`

Lista todas as moedas.

**Autenticação/Permissão:** token.

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": [
    { "id": "1", "name": "Euro", "symbol": "€", "rate": 0.9234, "code": "EUR" },
    { "id": "2", "name": "Dólar Americano", "symbol": "$", "rate": 1, "code": "USD" }
  ],
  "errors": null
}
```

**Erros:** ver a nota acima (sem token: 500 em vez de 401).

### `GET /api/v1/currencies/{id}`

Detalhe de uma moeda.

**Autenticação/Permissão:** token.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | Id da moeda |

**Resposta 200:** `{ "success": true, "message": "", "data": { "id": "1", "name": "Euro", "symbol": "€", "rate": 0.9234, "code": "EUR" }, "errors": null }`.

**Erros:** `500` — id inexistente. Os argumentos do `fail()` estão trocados: a resposta vem com `message: "Erro ao tentar buscar moeda."`, `errors` = texto da exceção e `meta: 0`.

### Rotas de escrita (só backoffice)

`POST /api/v1/currencies`, `PUT|PATCH /api/v1/currencies/{id}`, `DELETE /api/v1/currencies/{id}`, `GET /api/v1/currencies/check-code` e `GET /api/v1/currencies/update-rates` usam `auth` + `web`, ou seja, **sessão do backoffice** e não token Sanctum. Validam permissões globais com `allowedAction` (`createCurrency`, `editCurrency`, `destroyCurrency`, `viewCurrency`, `updateRates`). São chamadas pelo backoffice e não devem ser usadas pelo frontend/app. O body de criação/edição é `code` (3 letras, único), `symbol` e um campo por código de idioma (`pt`, `en`) com o nome. `update-rates` é um GET que corre `currency:fetch-daily`.

---

## Idiomas (`Modules/Language`)

### `GET /api/v1/languages`

Lista os idiomas suportados (tabela `languages`). Serve, por exemplo, para o seletor de idioma das preferências.

**Autenticação/Permissão:** token (`auth:sanctum` + `setlocale`).

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": [
    { "id": "1", "code": "pt", "name": "Português" },
    { "id": "2", "code": "en", "name": "Inglês" }
  ],
  "errors": null
}
```

`name` vem na língua de `user_preferences.lang` (`en` se não estiver definida).

**Erros:** `401` sem token. `500` em erro inesperado (`message: "Error try catch languages"`).

---

## Roles partilhados (`Modules/SharedRoles`)

### `GET /api/v1/shared-roles`

Lista os roles que podem ser atribuídos a membros e convites de contas, dívidas e metas (ver [sharing.md](sharing.md)). Exclui o role `creator`.

**Autenticação/Permissão:** token. Tem a mesma ordem de middleware `['setlocale', 'auth:sanctum']` das moedas (ver nota acima).

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": [
    { "id": "2", "name": "Editor", "code": "editor" },
    { "id": "3", "name": "Leitor", "code": "viewer" }
  ],
  "errors": null
}
```

- `name` vem na língua de `user_preferences.lang`.
- As migrations só semeiam o `creator`. Os restantes roles (e os seus códigos) são criados no backoffice, por isso os valores acima são ilustrativos e a lista pode vir vazia.

**Erros:** ver a nota de middleware. `500` em erro inesperado.

`GET /api/v1/shared-roles/check-code` e `GET /api/v1/shared-permissions/check-code` usam sessão do backoffice (`auth` + `web`) e não fazem parte da API do frontend.

---

## Ativos (`Modules/Asset`) — experimental

Catálogo de ações e ativos (dados obtidos via yfinance no backoffice). Não está integrado no resto do produto.

**Todas as rotas são públicas** (o middleware `auth:sanctum` está comentado, e com a gralha `middlware`). Não têm `setlocale`. Código: `Http/Controllers/Api/V1/AssetController.php` → `Repositories/AssetRepository.php`.

### `GET /api/v1/assets/search`

Pesquisa ativos por nome ou ticker na view `asset_search_view`. Ignora maiúsculas, espaços e hífens. Sem paginação.

**Autenticação/Permissão:** público.

**Parâmetros de query:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `search` | string | não | Texto a procurar (`LIKE %texto%` em `name` e `ticker`). Sem ele, devolve todos os ativos |

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": [
    { "id": 4, "ticker": "AAPL", "name": "Apple Inc.", "logo": "https://…/aapl.png", "currencySymbol": "$" }
  ],
  "errors": null
}
```

`name` vem de `fundamentals_json.shortName`. A view faz INNER JOIN com `currencies` pelo código, por isso os ativos cuja moeda não está em `currencies` não aparecem.

**Erros:** `400` — erro inesperado (`message: "Erro ao carregar asset."`).

### `GET /api/v1/assets/{ticker}/show`

Ativo completo por ticker.

**Autenticação/Permissão:** público.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `ticker` | string | sim | Ticker (ex.: `AAPL`) |

**Resposta 200:** `data` é o modelo `Asset` serializado tal como está (`parent::toArray`), com todas as colunas. Os campos `*_json` e `custom_fields` vêm como objetos/arrays.

```json
{
  "success": true,
  "message": "",
  "data": {
    "id": 4,
    "ticker": "AAPL",
    "name": "Apple Inc.",
    "country": "United States",
    "exchange": "NMS",
    "sector": "Technology",
    "website": "https://www.apple.com",
    "logo": "https://…/aapl.png",
    "price_json": { },
    "fundamentals_json": { "shortName": "Apple Inc." },
    "dividends_json": { },
    "dividends_history_json": [ ],
    "has_dividends": 1,
    "price": 228.5,
    "price_start_year": 185.2,
    "price_last_days_history_json": [ ],
    "currency": "USD",
    "news_json": [ ],
    "type": "EQUITY",
    "last_update": "2026-11-18 01:00:00",
    "last_update_dividends_info": null,
    "last_update_news": null,
    "custom_fields": null,
    "created_at": "2026-11-11T14:49:19.000000Z",
    "updated_at": "2026-11-18T01:00:00.000000Z"
  },
  "errors": null
}
```

A estrutura interna dos campos JSON depende do script `Modules/Asset/Scripts/finance_fetcher.py` e não está documentada aqui.

**Erros:** se o ticker não existir, devolve **200 com `data: []`** (o recurso é `null`). `400` em erro inesperado.

### `GET /api/v1/assets/{id}`

Ativo completo por id. A resposta é igual à de `/{ticker}/show`.

**Autenticação/Permissão:** público.

**Parâmetros de rota:** `id` (int, obrigatório).

**Erros:** `400` — id inexistente (`findOrFail` apanhado pelo controller; `message: "Erro ao carregar asset."`, `errors: {}`).

### `GET /api/v1/asset`

Rota registada para `AssetController@index`, que não existe. Responde sempre 500 (known-issues #5). Não usar.

## Listas administrativas de papéis e permissões

`GET /api/v1/shared-roles/paginated` e `GET /api/v1/shared-permissions` usam ModulePaginate, com auth:sanctum e autorização global viewSharedRole/viewSharedPermission. Aceitam code[eq], search pelo código e order_by=id/code; devolvem Resources JSON e pagination, sem HTML. `GET shared-roles` mantém o picker não paginado existente.
