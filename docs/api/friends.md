# API — Amigos

Amizades, bloqueios e pedidos de amizade (`Modules/Friends`). Convenções gerais (envelope, paginação, códigos de estado): ver [README.md](README.md). Regras de negócio: [../architecture/modules/user-friends-notification.md](../architecture/modules/user-friends-notification.md#friends-modulesfriends).

Todas as rotas usam `auth:sanctum` + `setlocale`. Em todas, **`{id}` é o id do outro utilizador**, nunca o id da amizade ou do pedido.

Código: `Http/Controllers/Api/FriendshipController.php`, `Http/Controllers/Api/FriendshipRequestController.php`, `Repositories/FriendshipRepository.php`, `Repositories/FriendshipRequestRepository.php`, `DataTables/`.

## Notas comuns

**Erros de negócio.** As exceções do módulo são devolvidas com o envelope `fail` (`success: false`, `errors: {}`) e o código definido na exceção. Muitas usam 500 em vez de 4xx (ver [known-issues](../architecture/known-issues.md)):

| Exceção | Código | Mensagem (pt) |
|---|---|---|
| `UserNotFoundException` | 404 | Utilizador não encontrado. |
| `FriendRequestNotFoundException` | 404 | Solicitação de amizade não encontrada. |
| `SelfBlockNotAllowedException` | 403 | Você não pode se bloquear. |
| `SenderCannotAcceptFriendRequestException` | 402 | O remetente não pode aceitar a própria solicitação de amizade. |
| `SelfFriendshipException` | 500 | Não é possível enviar, aceitar ou rejeitar solicitações para si mesmo. |
| `AlreadyFriendsException` | 500 | A amizade entre os usuários já existe. |
| `FriendRequestAlreadySentException` | 500 | Solicitação de amizade já enviada. |
| `FriendRequestLimitExceededException` | 500 | Você atingiu o número máximo de solicitações de amizade permitidas… |
| `UserBlockedException` | 500 | Usuário bloqueado. |
| `UserNotBlockedException` | 500 | Este usuário não está bloqueado. |
| `FriendshipNotFoundException` | 500 | Esta amizade não existe ou já foi removida. |

**Objeto `friendship` (`FriendshipResource`)**, devolvido por várias mutações. Também é usado para pedidos de amizade (decline/cancel):

```json
{
  "id": 31,
  "status": "friend",
  "created_at": "2026-11-18T10:21:04.000000Z",
  "updated_at": "2026-11-18T10:21:04.000000Z",
  "user": { "id": "12", "name": "Ana Silva", "username": "anasilva" }
}
```

`user` é sempre o **outro** utilizador (`UserShareResource`: `id` como string, `name`, `username`).

**Linhas sociais:** user usa dados públicos de UserShareResource. Pesquisa literal pelo outro participante, sem colunas SQL escolhidas pelo cliente.

---

## Amizades (`/api/v1/friendships`)

### `GET /api/v1/friendships`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

Amigos: o utilizador é sender ou receiver e status=friend. Pesquisa por nome/ID do outro participante, agrupada dentro do âmbito. Ordenação id/created_at; omissão id desc. user é UserShareResource (id, nome, username), sem email/preferências. Pedidos enviados permitem cancel; apenas pedidos recebidos permitem accept/decline.

---

### `GET /api/v1/friendships/blocked`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

Bloqueados: o utilizador participa e status=blocked. Pesquisa por nome/ID do outro participante, agrupada dentro do âmbito. Ordenação id/created_at; omissão id desc. user é UserShareResource (id, nome, username), sem email/preferências. Pedidos enviados permitem cancel; apenas pedidos recebidos permitem accept/decline.

---

### `GET /api/v1/friendships/stats`

Contadores para o ecrã de amigos (implementado em `FriendshipRequestController@stats`).

**Autenticação/Permissão:** token.

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": { "friends": 3, "received": 1, "sent": 2, "blocked": 1 },
  "errors": null
}
```

| Campo | Cálculo |
|---|---|
| `friends` | **Bug:** a query é `sender_id = me OR (receiver_id = me AND status = friend)`, por isso também conta os bloqueios feitos pelo utilizador |
| `received` | Pedidos `pending` recebidos |
| `sent` | Pedidos `pending` enviados |
| `blocked` | Bloqueios feitos pelo utilizador (`sender_id = me`) |

**Erros:** `401`, `500`.

---

### `DELETE /api/v1/friendships/{id}/remove`

Remove a amizade com o utilizador `{id}`.

**Autenticação/Permissão:** token.

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | Id do outro utilizador |

**Resposta 200:**

```json
{ "success": true, "message": "Amizade encerrada com sucesso.", "data": null, "errors": null }
```

**Erros:**
- `500` `FriendshipNotFoundException` — não existe nenhuma relação (`friend` ou `blocked`) entre os dois.
- `500` `UserBlockedException` — a relação é um bloqueio.

---

### `POST /api/v1/friendships/{id}/block`

Bloqueia o utilizador `{id}`. Apaga a amizade (se existir) e todos os pedidos entre os dois, e cria `friendships(sender = eu, receiver = {id}, status = blocked)`. Só quem bloqueou pode desbloquear.

**Autenticação/Permissão:** token.

**Parâmetros de rota:** `id` (int, obrigatório) — id do utilizador a bloquear.

**Body:** nenhum.

**Resposta 200:**

```json
{
  "success": true,
  "message": "Ana Silva bloqueado(a) com sucesso.",
  "data": {
    "id": 32,
    "status": "blocked",
    "created_at": "2026-11-18T10:30:00.000000Z",
    "updated_at": "2026-11-18T10:30:00.000000Z",
    "user": { "id": "7", "name": "Diogo Gonçalves", "username": "diogo" }
  },
  "errors": null
}
```

Provável bug: aqui `data.user` é o **próprio** utilizador e não o bloqueado. O `FriendshipResource` compara `sender_id === $request->user()->id` de forma estrita, mas `makeRelation(string $senderId, …)` grava `sender_id` como string. A comparação falha e é devolvido o `sender`. A `message` usa o `receiver` e está correta.

**Erros:**
- `403` `SelfBlockNotAllowedException` — `{id}` é o próprio.
- `500` `UserBlockedException` — já existe um bloqueio em qualquer sentido.
- `500` — `{id}` inexistente (falha de FK ao criar a linha).

---

### `POST /api/v1/friendships/{id}/unblock`

Remove o bloqueio que o utilizador autenticado fez a `{id}`.

**Autenticação/Permissão:** token.

**Parâmetros de rota:** `id` (int, obrigatório) — id do utilizador bloqueado.

**Resposta 200:**

```json
{ "success": true, "message": "Ana Silva desbloqueado(a) com sucesso.", "data": null, "errors": null }
```

**Erros:**
- `403` `SelfBlockNotAllowedException` — `{id}` é o próprio.
- `500` `UserNotBlockedException` — não há bloqueio entre os dois.
- `500` (`Server Error`, formato do handler do Laravel) — o bloqueio foi feito pelo **outro** utilizador: `isBlocked` aceita os dois sentidos, mas a linha procurada é `sender = eu`, que é `null`.

---

## Pedidos de amizade (`/api/v1/friendship-requests`)

### `GET /api/v1/friendship-requests/sent`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

Pedidos pending enviados pelo utilizador. Pesquisa por nome/ID do outro participante, agrupada dentro do âmbito. Ordenação id/created_at; omissão id desc. user é UserShareResource (id, nome, username), sem email/preferências. Pedidos enviados permitem cancel; apenas pedidos recebidos permitem accept/decline.

---

### `GET /api/v1/friendship-requests/received`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

Pedidos pending recebidos pelo utilizador. Pesquisa por nome/ID do outro participante, agrupada dentro do âmbito. Ordenação id/created_at; omissão id desc. user é UserShareResource (id, nome, username), sem email/preferências. Pedidos enviados permitem cancel; apenas pedidos recebidos permitem accept/decline.

---

### `POST /api/v1/friendship-requests/{id}/send`

Envia um pedido de amizade a `{id}` e cria a notificação `FRIEND_REQUEST_RECEIVED` para o destinatário (ver [notifications.md](notifications.md)).

**Autenticação/Permissão:** token.

**Parâmetros de rota:** `id` (int, obrigatório) — id do destinatário.

**Body:** nenhum.

**Resposta 200:** `data` é o modelo `FriendshipRequestModel` serializado tal como está (`parent::toArray`). Inclui `receiver` porque a relação é carregada para montar a mensagem. Isso expõe o `email` do destinatário.

```json
{
  "success": true,
  "message": "Pedido de amizade enviado com sucesso para Rui Costa.",
  "data": {
    "sender_id": 7,
    "receiver_id": "15",
    "status": "pending",
    "updated_at": "2026-11-18T10:40:00.000000Z",
    "created_at": "2026-11-18T10:40:00.000000Z",
    "id": 55,
    "receiver": {
      "id": 15,
      "name": "Rui Costa",
      "email": "rui@example.com",
      "email_verified_at": "2026-10-01T12:00:00.000000Z",
      "username": "rcosta",
      "username_updated_at": null,
      "created_at": "2026-10-01T11:58:00.000000Z",
      "updated_at": "2026-10-01T12:00:00.000000Z"
    }
  },
  "errors": null
}
```

(`receiver_id` vem como string porque é o valor da rota.)

**Erros** (verificados por esta ordem):
- `404` `UserNotFoundException` — `{id}` não existe.
- `500` `SelfFriendshipException` — `{id}` é o próprio.
- `500` `AlreadyFriendsException`.
- `500` `FriendRequestAlreadySentException` — já há um pedido pendente em qualquer sentido.
- `500` `UserBlockedException` — bloqueio em qualquer sentido.
- `500` `FriendRequestLimitExceededException` — 3 recusas em 30 dias. **Nunca dispara:** filtra `status = 'decline'`, mas o valor gravado é `declined` (known-issues #2).
- `500` — o tipo de notificação `FRIEND_REQUEST_RECEIVED` não existe em `notification_types` (FK). A transação é revertida e o pedido não é criado.

---

### `POST /api/v1/friendship-requests/{id}/accept`

Aceita o pedido pendente enviado por `{id}`. Cria `friendships(sender = {id}, receiver = eu, status = friend)`, apaga o pedido e notifica `{id}` com `FRIEND_REQUEST_ACCEPTED`.

**Autenticação/Permissão:** token.

**Parâmetros de rota:** `id` (int, obrigatório) — id de quem enviou o pedido.

**Resposta 200:**

```json
{
  "success": true,
  "message": "Pedido de amizade de Rui Costa aceite com sucesso.",
  "data": {
    "id": 33,
    "status": "friend",
    "created_at": "2026-11-18T10:45:00.000000Z",
    "updated_at": "2026-11-18T10:45:00.000000Z",
    "user": { "id": "15", "name": "Rui Costa", "username": "rcosta" }
  },
  "errors": null
}
```

**Erros:**
- `500` `SelfFriendshipException`, `AlreadyFriendsException`, `UserBlockedException`.
- `404` `FriendRequestNotFoundException` — não há pedido pendente em nenhum sentido.
- `402` `SenderCannotAcceptFriendRequestException` — o pedido pendente foi enviado pelo próprio utilizador.
- `500` — tipo `FRIEND_REQUEST_ACCEPTED` inexistente (a transação é revertida).

---

### `DELETE /api/v1/friendship-requests/{id}/decline`

Recusa o pedido pendente enviado por `{id}`. O pedido passa a `declined` (não é apagado) e `{id}` é notificado com `FRIEND_REQUEST_DECLINED`.

**Autenticação/Permissão:** token.

**Parâmetros de rota:** `id` (int, obrigatório) — id de quem enviou o pedido.

**Resposta 200** (`FriendshipResource` aplicado ao pedido):

```json
{
  "success": true,
  "message": "Pedido de amizade de Rui Costa rejeitado com sucesso.",
  "data": {
    "id": 55,
    "status": "declined",
    "created_at": "2026-11-18T10:40:00.000000Z",
    "updated_at": "2026-11-18T10:50:00.000000Z",
    "user": { "id": "15", "name": "Rui Costa", "username": "rcosta" }
  },
  "errors": null
}
```

**Erros:**
- `500` `SelfFriendshipException`, `UserBlockedException`.
- `404` `FriendRequestNotFoundException` — não há pedido pendente em nenhum sentido.
- `500` (`Server Error`) — o pedido pendente foi enviado pelo **próprio** utilizador: a verificação aceita os dois sentidos, mas a linha procurada é `sender = {id}`. Para retirar um pedido enviado usa-se `cancel`.
- `500` — tipo `FRIEND_REQUEST_DECLINED` inexistente.

---

### `DELETE /api/v1/friendship-requests/{id}/cancel`

Cancela (apaga) o pedido pendente que o utilizador enviou a `{id}`. Não gera notificação.

**Autenticação/Permissão:** token.

**Parâmetros de rota:** `id` (int, obrigatório) — id do destinatário do pedido.

**Resposta 200** (`FriendshipResource` aplicado ao pedido apagado):

```json
{
  "success": true,
  "message": "Pedido de amizade para Rui Costa cancelado com sucesso.",
  "data": {
    "id": 55,
    "status": "pending",
    "created_at": "2026-11-18T10:40:00.000000Z",
    "updated_at": "2026-11-18T10:40:00.000000Z",
    "user": { "id": "15", "name": "Rui Costa", "username": "rcosta" }
  },
  "errors": null
}
```

**Erros:**
- `500` `SelfFriendshipException`, `UserBlockedException`.
- `404` `FriendRequestNotFoundException`.
- `500` (`Server Error`) — o pedido pendente foi recebido (não enviado) pelo utilizador. Nesse caso usa-se `decline`.
