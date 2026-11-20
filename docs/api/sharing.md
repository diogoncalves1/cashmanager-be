# API — Partilha: convites e membros (`accounts`, `debts`, `financial-goals`)

Endpoints genéricos de convites e gestão de membros, comuns a contas, dívidas e metas. Convenções gerais (envelope, paginação, códigos de estado) em [README.md](README.md). Fluxo e invariantes em [../architecture/auth-and-permissions.md](../architecture/auth-and-permissions.md#fluxo-de-partilha-e-convites).

Em todos os caminhos, `{resource}` ∈ `accounts` | `debts` | `financial-goals`. A lógica é a mesma nos três módulos (`*UserInviteController` / `*UserInviteRepository` e `*UserController` / `*UserRepository`); as diferenças estão descritas na tabela seguinte e em cada endpoint.

Na refatoração local, as Resources de contas estão em `Http/Resources/Accounts/`. O objeto `account` aninhado usa a nova AccountResource: omite `currencyId` e só inclui `currency` quando essa relação está carregada. As listas de convites usam o contrato comum ModulePaginate. Erros que passam pelo handler global seguem a normalização de [convenções](README.md).

## Diferenças por recurso

| | `accounts` | `debts` | `financial-goals` |
|---|---|---|---|
| Permissão de gestão (`{manage}`) | `manageAccountUsers` | `manageDebtUsers` | `manageFinancialGoalUsers` |
| Recurso inexistente | 404 `AccountNotFoundException` | 404 `DebtNotFoundException` | **500** (`ModelNotFoundException` de `findOrFail` apanhada como `\Exception`, código 0 → 500, mensagem `No query results for model …`) |
| Erro de validação de `shared_role_id` | formato Laravel | formato Laravel | `{"success": false, "message": "Validation Error", "errors": {…}}` |
| `data` de `invite` | `AccountUserInviteResource` | modelo `DebtUserInvite` cru (snake_case) + relações `debt` e `user` | `FinancialGoalUserInviteResource` |
| `data` de `accept` | `AccountUserResource` | pivot `DebtUser` cru + relação `debt` | `FinancialGoalUserResource` |
| `data` de `user-role` | `AccountUserResource` | `DebtUser` cru + relações `debt` e `user` | `FinancialGoalUserResource` |
| `subject` nas listas de convites | ⚠️ `DebtResource` aplicado a uma `Account` (bug: quase tudo `null`) | `DebtResource` | `FinancialGoalResource` |
| Ordem das verificações em `user-role` e `leave` | ver endpoint | igual a `accounts` | diferente (ver endpoint) |
| Log (`activity_logs.type`) | `account` | `debt` | `financial_goal` |

Notas comuns:

- Os controllers apanham `\Exception` e devolvem `fail($e->getMessage(), $e, $e->getCode())`. As exceções de `Modules/SharedRoles/Exceptions` usam códigos pouco habituais: **401** para falta de permissão (não 403) e **500** para regras de negócio. O `errors` traz a exceção serializada.
- Se o utilizador autenticado **não for membro**, alguns caminhos chamam `->code` sobre um role `null` (PHP `Error`, não apanhado): resposta 500 genérica do Laravel. Assinalado como "não membro → 500".
- Os convites **não** geram notificações. Cada passo regista um evento no activity log do recurso.

---

## Convites

### `POST /api/v1/{resource}/{id}/invite/{userId}`

Convida um amigo para o recurso com um role partilhado. Cria um convite `pending` e regista `user_invited`. **Permissão:** `{manage}`.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | ID do recurso |
| `userId` | int | sim | Utilizador a convidar (tem de ser amigo) |

**Body** (`InviteUser{Account,Debt,FinancialGoal}Request`):

| Campo | Regras | Descrição |
|---|---|---|
| `shared_role_id` | `required\|exists:shared_roles,id` | Role a atribuir. Não pode ser `creator` |

```json
{ "shared_role_id": 3 }
```

**Resposta 200** — `accounts` (`AccountUserInviteResource`):

```json
{
  "success": true,
  "message": "…",
  "data": {
    "sharedRole": { "id": "3", "name": "Editor", "code": "editor" },
    "account": { "id": 2, "name": "Ordenado", "type": "bank_account", "balance": 2150.4, "active": 1 },
    "user": { "id": "4", "name": "Ana", "username": "ana" },
    "status": "pending",
    "statusTranslated": "Pendente",
    "invitedAt": "2026-11-18T10:00:00.000000Z"
  },
  "errors": null
}
```

**Resposta 200** — `debts` (modelo cru; `debt` e `user` são carregados pela mensagem e serializados com todos os atributos, exceto `password`/`remember_token` do utilizador):

```json
{
  "success": true,
  "message": "O utilizador Ana foi convidado com sucesso para a dívida Crédito automóvel.",
  "data": {
    "shared_role_id": 3,
    "debt_id": "3",
    "invited_by_id": 1,
    "user_id": "4",
    "status": "pending",
    "updated_at": "2026-11-18T10:00:00.000000Z",
    "created_at": "2026-11-18T10:00:00.000000Z",
    "id": 12,
    "debt": { "id": 3, "name": "Crédito automóvel", "...": "…" },
    "user": { "id": 4, "name": "Ana", "username": "ana", "email": "ana@example.com", "...": "…" }
  },
  "errors": null
}
```

**Resposta 200** — `financial-goals` (`FinancialGoalUserInviteResource`):

```json
{
  "success": true,
  "message": "O usuário Ana foi convidado com sucesso para a meta financeira Férias Açores.",
  "data": {
    "sharedRole": { "id": "3", "name": "Editor", "code": "editor" },
    "subject": { "id": 7, "currencyId": 1, "name": "Férias Açores", "totalAmount": 1500, "contributedAmount": 600, "startDate": "2026-01-01", "dueDate": "2027-06-30", "status": "in_progress", "description": null, "priority": "high", "createdAt": "2026-01-02T09:10:11.000000Z" },
    "receiver": { "id": "4", "name": "Ana", "username": "ana" },
    "sender": { "id": "1", "name": "Diogo", "username": "diogo" },
    "status": "pending",
    "statusTranslated": "Pendente",
    "invitedAt": "2026-11-18T10:00:00.000000Z"
  },
  "errors": null
}
```

**Erros** (pela ordem em que são verificados):
1. 422 — validação de `shared_role_id`.
2. 404/500 — recurso inexistente (ver tabela).
3. 500 — `CreatorInviteException` (role `creator`).
4. 500 — `FriendshipNotFoundException` (não são amigos).
5. 500 — `SelfInviteException` (convidar-se a si próprio).
6. 500 — `InvitesLimitExceededException` (3 ou mais convites `revoked` para este utilizador e recurso nos últimos 30 dias).
7. 500 — `InviteAlreadySentException` (já há convite `pending`).
8. 500 — `AlreadyRelationException` (já é membro).
9. 401 — `InviteUserNotAllowedException` (sem `{manage}` ou não membro).

### `POST /api/v1/{resource}/{id}/accept`

O utilizador autenticado aceita o seu convite `pending`: passa a membro com o role do convite e o convite fica `accepted`. Regista `user_joined`. **Permissão:** nenhuma (ser o convidado).

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200** — `accounts` (`AccountUserResource`):

```json
{
  "success": true,
  "message": "…",
  "data": {
    "sharedRole": { "id": "3", "name": "Editor", "code": "editor" },
    "account": { "id": 2, "name": "Ordenado", "type": "bank_account", "balance": 2150.4, "active": 1 },
    "user": { "id": "4", "name": "Ana", "username": "ana" },
    "createdAt": "2026-11-18T10:05:00.000000Z"
  },
  "errors": null
}
```

**Resposta 200** — `debts` (pivot `DebtUser` cru, sem `id` porque o pivot não é auto-incremental no modelo, + relação `debt`):

```json
{
  "success": true,
  "message": "Convite para a dívida Crédito automóvel aceite com sucesso.",
  "data": {
    "debt_id": "3",
    "user_id": 4,
    "shared_role_id": 3,
    "updated_at": "2026-11-18T10:05:00.000000Z",
    "created_at": "2026-11-18T10:05:00.000000Z",
    "debt": { "id": 3, "name": "Crédito automóvel", "...": "…" }
  },
  "errors": null
}
```

**Resposta 200** — `financial-goals` (`FinancialGoalUserResource`):

```json
{
  "success": true,
  "message": "Convite para a meta financeira Férias Açores aceito com sucesso.",
  "data": {
    "sharedRole": { "id": "3", "name": "Editor", "code": "editor" },
    "financialGoal": { "id": 7, "name": "Férias Açores", "...": "FinancialGoalResource" },
    "user": { "id": "4", "name": "Ana", "username": "ana" },
    "status": null,
    "createdAt": "2026-11-18T10:05:00.000000Z"
  },
  "errors": null
}
```

`status` vem sempre `null` (a tabela `financial_goal_users` não tem essa coluna).

**Erros:**
- 500 — `AlreadyRelationException` (já é membro).
- 404 — `InviteNotFoundException` (sem convite `pending`).

Este endpoint não verifica se o recurso existe (o convite `pending` já o garante).

### `DELETE /api/v1/{resource}/{id}/invite/{userId}`

Um gestor cancela (apaga) um convite `pending` enviado a `userId`. Regista `invited_destroyed`. **Permissão:** `{manage}`.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | ID do recurso |
| `userId` | int | sim | Utilizador convidado |

**Resposta 200:**

```json
{ "success": true, "message": "O convite para a dívida Crédito automóvel do utilizador Ana foi eliminado com sucesso.", "data": null, "errors": null }
```

**Erros:**
- 404/500 — recurso inexistente (ver tabela).
- 404 — `InviteNotFoundException` (sem convite `pending` para `userId`).
- 401 — `UnauthorizedDestroyInviteException` (sem `{manage}` ou não membro).

### `POST /api/v1/{resource}/{id}/revoke`

O utilizador autenticado **recusa** o seu convite `pending` (passa a `revoked`). Regista `invited_revoked`. Três recusas em 30 dias bloqueiam novos convites para o mesmo recurso. **Permissão:** nenhuma (ser o convidado).

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200:**

```json
{ "success": true, "message": "Convite para a dívida Crédito automóvel rejeitado.", "data": null, "errors": null }
```

**Erros:**
- 404/500 — recurso inexistente (ver tabela).
- 404 — `InviteNotFoundException`.

### `GET /api/v1/{resource}/invitations-stats`

Contagens de convites do utilizador autenticado para esse tipo de recurso. **Permissão:** nenhuma.

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": {
    "sentInvites": 5,
    "receivedInvites": 2,
    "pendingInvites": 1,
    "awaitingInvites": 1
  },
  "errors": null
}
```

- `sentInvites`: enviados pelo utilizador (qualquer estado). `pendingInvites`: enviados e ainda `pending`.
- `receivedInvites`: recebidos (qualquer estado). `awaitingInvites`: recebidos e ainda `pending`.

### `GET /api/v1/{resource}/sent-invitations`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

Cada domínio injeta o seu `<Entity>UserInvitePaginate`, Filter, Search e ListResource. Enviados restringem invited_by_id; recebidos restringem user_id. Aceitam status[eq] (ou status escalar), search por remetente/destinatário/nome do subject e order_by=id/created_at/status. Omissão por id desc. Linhas apresentam sender, receiver, subject do domínio correto, sharedRole, createdAt e status/statusTranslated. Não há HTML do backoffice nem dados de convites de terceiros.

---

### `GET /api/v1/{resource}/received-invitations`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

Cada domínio injeta o seu `<Entity>UserInvitePaginate`, Filter, Search e ListResource. Enviados restringem invited_by_id; recebidos restringem user_id. Aceitam status[eq] (ou status escalar), search por remetente/destinatário/nome do subject e order_by=id/created_at/status. Omissão por id desc. Linhas apresentam sender, receiver, subject do domínio correto, sharedRole, createdAt e status/statusTranslated. Não há HTML do backoffice nem dados de convites de terceiros.

---

## Membros

### `POST /api/v1/{resource}/{id}/revoke-user/{userId}`

Um gestor remove um membro. Regista `user_revoked`. **Permissão:** `{manage}`.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | ID do recurso |
| `userId` | int | sim | Membro a remover |

**Resposta 200:**

```json
{ "success": true, "message": "O utilizador Ana foi removido da dívida Crédito automóvel.", "data": null, "errors": null }
```

**Erros** (mesma ordem nos três recursos):
1. 404/500 — recurso inexistente (ver tabela).
2. 500 — `RelationNotExistsException` (`userId` não é membro).
3. 500 — `CreatorRevokeException` (`userId` é o creator).
4. 401 — `UnauthorizedRevokeUserException` (sem `{manage}` ou não membro).
5. 500 — remover-se a si próprio: o código lança `new \Exception()` sem mensagem (`message: ""`). Para sair usa-se `leave`.

### `PUT /api/v1/{resource}/{id}/user-role/{userId}`

Um gestor muda o role de um membro. Regista `user_role_updated`. **Permissão:** `{manage}`.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | ID do recurso |
| `userId` | int | sim | Membro a alterar |

**Body** (mesmo FormRequest do convite):

| Campo | Regras | Descrição |
|---|---|---|
| `shared_role_id` | `required\|exists:shared_roles,id` | Novo role. Não pode ser `creator` |

```json
{ "shared_role_id": 4 }
```

**Resposta 200:**
- `accounts`: `AccountUserResource` (ver `accept`).
- `financial-goals`: `FinancialGoalUserResource` (ver `accept`).
- `debts`: `DebtUser` cru (com `id`, `debt_id`, `user_id`, `shared_role_id`, `created_at`, `updated_at`) + relações `debt` e `user`.

```json
{
  "success": true,
  "message": "O papel do utilizador Ana foi atualizado na dívida Crédito automóvel.",
  "data": {
    "id": 9,
    "debt_id": 3,
    "user_id": 4,
    "shared_role_id": 4,
    "created_at": "2026-11-18T10:05:00.000000Z",
    "updated_at": "2026-11-18T11:00:00.000000Z",
    "debt": { "id": 3, "name": "Crédito automóvel", "...": "…" },
    "user": { "id": 4, "name": "Ana", "...": "…" }
  },
  "errors": null
}
```

**Erros — `accounts` e `debts`** (por esta ordem):
1. 422 — validação.
2. 404 — recurso inexistente.
3. 500 — `RelationNotExistsException` (`userId` não é membro).
4. 500 — `SelfRoleUpdateNotAllowedException` (alterar o próprio role).
5. 500 — `SingleAccountCreatorViolationException` / `SingleDebtCreatorViolationException` (novo role é `creator`).
6. 401 — `UnauthorizedUpdateUserRoleException` (quem altera tem o mesmo role que o alvo). Se quem altera não for membro → 500 (role `null`).
7. 401 — `UnauthorizedUpdateUserRoleException` (sem `{manage}`, ou o alvo é o creator).

**Erros — `financial-goals`** (por esta ordem):
1. 422 — `Validation Error`.
2. 500 — meta inexistente.
3. 500 — `RelationNotExistsException`.
4. 500 — `SelfRoleUpdateNotAllowedException`.
5. 401 — `UnauthorizedUpdateUserRoleException` (sem `{manage}`, não membro, ou o alvo é o creator).
6. 500 — `SingleFinancialGoalCreatorViolationException` (novo role é `creator`).
7. 401 — `UnauthorizedUpdateUserRoleException` (mesmo role que o alvo).

### `DELETE /api/v1/{resource}/{id}/leave`

O utilizador autenticado sai do recurso. O creator não pode sair. Regista `user_leaved`. **Permissão:** membro.

**Parâmetros de rota:** `id` (int, obrigatório). **Body:** nenhum.

**Resposta 200:**

```json
{ "success": true, "message": "Saiu da dívida Crédito automóvel com sucesso.", "data": null, "errors": null }
```

**Erros:**
- `accounts` e `debts`: 404 recurso inexistente → 500 `CreatorCantLeaveException` → 500 `RelationNotExistsException`. Como o role é lido antes de verificar a pertença, um **não membro** recebe um 500 genérico (role `null`) em vez de `RelationNotExistsException`.
- `financial-goals`: 500 meta inexistente → 500 `RelationNotExistsException` → 500 `CreatorCantLeaveException`.
