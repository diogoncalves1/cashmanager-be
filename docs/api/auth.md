# API — Autenticação

Registo, login/logout, utilizador autenticado, verificação de email e recuperação/alteração de password. Convenções gerais (envelope, códigos de estado): ver [README.md](README.md).

Código: `routes/api.php` → `app/Http/Controllers/Api/V1/AuthController.php`, `Modules/User/Repositories/UserRepository.php`, `app/Http/Requests/Auth/*`.

> Estas rotas **não** têm o middleware `setlocale`. As mensagens fixas estão em português e hard-coded no controller, e o nome da moeda em `preferences.currency.name` vem no locale por defeito da app (`APP_LOCALE`, `en`), não no idioma do utilizador.

## Objeto `user` (`UserResource`)

Devolvido por `POST /login`, `GET /me` e `PUT /me` ([users.md](users.md)).

```json
{
  "id": 7,
  "username": "diogo",
  "name": "Diogo Gonçalves",
  "email": "diogo@example.com",
  "preferences": {
    "lang": "pt",
    "currency_id": "1",
    "currency": { "id": "1", "name": "Euro", "symbol": "€", "rate": 0.9234, "code": "EUR" }
  }
}
```

- `id` é numérico (não é convertido para string).
- `roles` só aparece se a relação estiver carregada (nunca acontece nestes endpoints). `sharedRole` só aparece quando o modelo tem esse atributo (listagens de membros de recursos partilhados).
- `preferences.currency_id` vem como string.

## Fluxos por email (links para o frontend)

Os links usam `config('app.frontend_url')` (`FRONTEND_URL`). O `token` do link é um **token Sanctum pessoal** (`createToken('web-token')`), igual ao devolvido pelo login. O frontend deve lê-lo da query string e enviá-lo como `Authorization: Bearer <token>`.

| Fluxo | Disparo | Email (código em `email_types`) | Link | Chamada seguinte do frontend |
|---|---|---|---|---|
| Verificação de email | `POST /register` → `UserObserver::created` → evento `VerifyEmail` | `verify_email` | `{FRONTEND_URL}/verify-email?token=<token>` | `POST /verify-email` com o token como Bearer |
| Recuperação de password | `POST /reset-password` → evento `ResetPassword` | `reset_password` | `{FRONTEND_URL}/change-password?token=<token>` | `POST /reset-change-password` com o token como Bearer |
| Confirmação de verificação | `POST /verify-email` → evento `EmailVerified` | `email_verified` | — | — |
| Aviso de password alterada | `POST /change-password` ou `POST /reset-change-password` → evento `PasswordChanged` | `password_changed` | — | — |

**Atenção (segurança):** os tokens enviados por email não expiram (`sanctum.expiration = null`), não são revogados depois de usados e dão acesso a **toda** a API, não só à ação do link. Um utilizador com email por verificar não consegue fazer login, mas o token do email de verificação já lhe dá acesso autenticado.

---

### `POST /api/v1/register`

Cria um utilizador. O `UserObserver` cria as preferências (`currency_id = 1`, `lang = 'en'`) e envia o email de verificação. O login só funciona depois de `POST /verify-email`.

**Autenticação/Permissão:** público.

**Body:**

| Campo | Regras | Descrição |
|---|---|---|
| `email` | `required\|email\|unique:users\|max:191` | Única regra validada |
| `name` | sem validação (coluna `NOT NULL`) | Nome. Se faltar, o utilizador não é criado (ver nota) |
| `password` | sem validação | Guardada com `Hash::make`. Não há tamanho mínimo |
| `username` | sem validação (coluna única, nullable) | Opcional |

Qualquer outro campo do `fillable` de `User` é aceite (incluindo `username_updated_at`). `roles` é ignorado no input, mas é feito `sync(null)`.

```json
{
  "name": "Diogo Gonçalves",
  "email": "diogo@example.com",
  "password": "uma-password-segura",
  "username": "diogo"
}
```

**Resposta 200:**

```json
{ "success": true, "message": "", "data": null, "errors": null }
```

**Erros:**
- `500` — email inválido, repetido ou em falta. A `ValidationException` é apanhada pelo `catch (\Exception)` genérico e devolvida com o código 0 → 500, com `errors: {}` (não segue o formato 422 do Laravel).
- **Nota:** `UserRepository::store` apanha todas as exceções e devolve `null`. Se a criação falhar (ex.: falta `name`, `username` repetido, falha no envio do email de verificação, que é síncrono e corre dentro da transação), a resposta continua a ser **200** e o utilizador não é criado.

---

### `POST /api/v1/login`

Autentica com email e password e devolve um token Sanctum (`web-token`).

**Autenticação/Permissão:** público.

**Body:**

| Campo | Regras | Descrição |
|---|---|---|
| `email` | `required\|email` | Email do utilizador |
| `password` | `required\|string` | Password |

```json
{ "email": "diogo@example.com", "password": "uma-password-segura" }
```

**Resposta 201** (sem envelope):

```json
{
  "user": {
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
  "token": "12|Xr8c3k...plainTextToken"
}
```

**Erros:**
- `422` — campos em falta ou credenciais inválidas. Usa o **envelope** (não o formato Laravel): `{"success": false, "message": "Credenciais invalidas.", "data": null, "errors": {"email": ["Credenciais invalidas."]}}`.
- `403` — email por verificar: `message: "Valide o seu email antes de efetuar login."`, `errors: {}`.

Não existe rate limiting neste endpoint (o `LoginRequest` com throttle é do backoffice e não é usado aqui).

---

### `POST /api/v1/logout`

Apaga o token usado no pedido.

**Autenticação/Permissão:** token.

**Body:** nenhum.

**Resposta 200** (sem envelope):

```json
{ "message": "Logout realizado com successo" }
```

**Erros:** `401` sem token.

---

### `GET /api/v1/me`

Devolve o utilizador autenticado.

**Autenticação/Permissão:** token.

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
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

**Erros:** `401` sem token.

A atualização do perfil (`PUT /me`) está em [users.md](users.md).

---

### `POST /api/v1/verify-email`

Marca o email do dono do token como verificado. É chamado pelo frontend na página `/verify-email?token=…`, com o token do link como Bearer. Dispara `Illuminate\Auth\Events\Verified` e `EmailVerified` (email `email_verified`).

**Autenticação/Permissão:** token (o do link).

**Body:** nenhum.

**Resposta 200:** `{ "success": true, "message": "", "data": null, "errors": null }`. Se o email já estiver verificado, devolve o mesmo, sem disparar eventos.

**Erros:**
- `401` — token inválido.
- `500` — erro inesperado (ex.: falha no envio do email).

---

### `POST /api/v1/reset-password`

Envia o email de recuperação com o link `{FRONTEND_URL}/change-password?token=<token>`.

**Autenticação/Permissão:** público.

**Body:**

| Campo | Regras | Descrição |
|---|---|---|
| `email` | sem validação | Email da conta |

```json
{ "email": "diogo@example.com" }
```

**Resposta 200:** `{ "success": true, "message": "", "data": null, "errors": null }`.

**Erros:**
- `500` — email em falta ou sem conta associada: `getUserByEmail(null)` / `new ResetPassword(null)` lançam `TypeError`, que não é apanhado pelo `catch (\Exception)` e chega ao handler do Laravel. A diferença entre 200 e 500 permite descobrir que emails existem.

---

### `POST /api/v1/reset-change-password`

Define uma nova password para o dono do token. O frontend chama-o a partir de `/change-password?token=…`, com o token do link como Bearer. Não pede a password atual. Dispara `PasswordChanged` (email `password_changed`).

**Autenticação/Permissão:** token (o do link).

**Body** (`AuthResetChangePasswordRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `password` | `required\|string\|confirmed` | Nova password. Sem tamanho mínimo |
| `password_confirmation` | (exigido por `confirmed`) | Tem de ser igual a `password` |

```json
{ "password": "nova-password", "password_confirmation": "nova-password" }
```

**Resposta 200:** `{ "success": true, "message": "", "data": null, "errors": null }`.

**Erros:**
- `401` — token inválido.
- `422` — validação (formato Laravel).

O token não é revogado depois da alteração, e os outros tokens do utilizador também continuam válidos.

---

### `POST /api/v1/change-password`

Altera a password do utilizador autenticado, confirmando a atual. Dispara `PasswordChanged`.

**Autenticação/Permissão:** token.

**Body** (`AuthChangePasswordRequest`):

| Campo | Regras | Descrição |
|---|---|---|
| `current_password` | `required\|string` | Password atual |
| `password` | `required\|string\|confirmed` | Nova password. Sem tamanho mínimo |
| `password_confirmation` | (exigido por `confirmed`) | Tem de ser igual a `password` |

```json
{
  "current_password": "uma-password-segura",
  "password": "nova-password",
  "password_confirmation": "nova-password"
}
```

**Resposta 200:** `{ "success": true, "message": "", "data": null, "errors": null }`.

**Erros:**
- `401` — sem token.
- `403` — password atual errada: `message: "Palavra-Passe atual inválida."`, `errors: {}`.
- `422` — validação (formato Laravel).
