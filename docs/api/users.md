# API — Utilizadores e preferências

Atualização do perfil, pesquisa de utilizadores, verificação de username (`Modules/User`) e preferências de idioma/moeda (`Modules/UserPreferences`). Convenções gerais (envelope, paginação, códigos de estado): ver [README.md](README.md).

O objeto `user` (`UserResource`) está descrito em [auth.md](auth.md#objeto-user-userresource). `GET /me` também está em [auth.md](auth.md).

---

### `PUT /api/v1/me`

Atualiza o nome, email e username do utilizador autenticado e, no mesmo pedido, as preferências (`currency_id`, `lang`). Se o `username` mudar, grava `username_updated_at = now()`. No fim aplica o novo `lang` ao locale do pedido.

Código: `Modules/User/Http/Controllers/Api/UserController@updateSettings` → `UserRepository::updateSettings`.

**Autenticação/Permissão:** token.

**Body:** o controller recebe um `Request` simples, **sem validação**. Campos lidos:

| Campo | Regras | Descrição |
|---|---|---|
| `name` | nenhuma | Nome |
| `email` | nenhuma (coluna única) | Email. Não volta a pedir verificação |
| `username` | nenhuma (coluna única) | Username. Se for diferente do atual, atualiza `username_updated_at` |
| `currency_id` | nenhuma (FK `currencies.id`) | Moeda preferida |
| `lang` | nenhuma, mas **obrigatório na prática** | Idioma (`pt`, `en`). Sem ele, `App::setLocale($preferencesInput['lang'])` rebenta e a transação é revertida |

Campos omitidos não são alterados (é usado `$request->only`). Exceção: se `username` for omitido e o atual não for `null`, `username_updated_at` é atualizado na mesma.

```json
{
  "name": "Diogo Gonçalves",
  "email": "diogo@example.com",
  "username": "diogo",
  "currency_id": 1,
  "lang": "pt"
}
```

**Resposta 200:**

```json
{
  "success": true,
  "message": "Definições de utilizador atualizadas com sucesso.",
  "data": {
    "id": 7,
    "username": "diogo",
    "name": "Diogo Gonçalves",
    "email": "diogo@example.com",
    "preferences": {
      "lang": "pt",
      "currency_id": "1",
      "currency": { "id": "1", "name": "Euro", "symbol": "€", "rate": 0.9234, "code": "EUR" }
    }
  },
  "errors": null
}
```

A `message` vem no idioma **anterior** (o `setlocale` corre antes da alteração). Provável bug: o middleware `setlocale` já carregou `$user->preferences`, e o repository atualiza as preferências com uma query (`preferences()->update`), sem recarregar a relação. Por isso, `data.preferences.lang` e `data.preferences.currency_id` podem vir com os valores **antigos**. O frontend deve usar os valores que enviou ou chamar `GET /me`.

**Erros:**
- `401` — sem token.
- `500` — `lang` em falta, `email`/`username` já usados, ou `currency_id` inexistente (exceções de BD). Como não há validação, nenhum destes casos devolve 422.

---

### `GET /api/v1/users/search`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

`SearchUserPaginate` exclui o próprio utilizador. `search` procura nome, username ou ID; ordenação id/name/username, por id descendente por omissão. Linhas limitam-se a id, name, username e status (friend/pending/blocked ou vazio); não incluem email, password, preferências ou timestamps privados.

---

### `GET /api/v1/check-username`

Indica se um username já está ocupado.

**Autenticação/Permissão:** público.

**Parâmetros de query:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `username` | string | sim (não validado) | Username a verificar (comparação exata) |

**Resposta 200:**

```json
{ "success": true, "message": "", "data": { "exists": true }, "errors": null }
```

Sem `username`, a query fica `WHERE username IS NULL` e devolve `exists: true` se houver algum utilizador sem username. Também não exclui o próprio utilizador: o frontend deve ignorar `exists: true` quando o valor é o username atual.

**Erros:** `500` em erro inesperado.

---

### `PUT /api/v1/user-preferences`

Atualiza só as preferências do utilizador autenticado (tabela `user_preferences`).

Código: `Modules/UserPreferences/Http/Controllers/Api/UserPreferencesController@update` → `UserPreferencesRepository::update`.

**Autenticação/Permissão:** token.

**Body** (`UserPreferencesRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `lang` | `nullable\|string\|in:pt,en` | Idioma da interface, das mensagens da API e dos nomes traduzidos |
| `currency_id` | `nullable\|exists:currencies,id` | Moeda principal (dashboard, saldo total, conversões) |

Os campos omitidos não são alterados. Se forem enviados com `null`, a coluna é posta a `null`.

```json
{ "lang": "en", "currency_id": 2 }
```

**Resposta 200:**

```json
{
  "success": true,
  "message": "Preferências do utilizador atualizadas com sucesso.",
  "data": {
    "lang": "en",
    "currency_id": "2",
    "currency": { "id": "2", "name": "Dólar Americano", "symbol": "$", "rate": 1, "code": "USD" }
  },
  "errors": null
}
```

A `message` e o `currency.name` vêm no idioma **anterior** à alteração (o locale é aplicado pelo `setlocale` antes do update). Os valores de `data` já são os novos.

**Erros:**
- `401` — sem token.
- `422` — validação (formato Laravel).
- `500` — erro ao gravar: `message: "Erro ao tentar atualizar as preferências do utilizador."`.
