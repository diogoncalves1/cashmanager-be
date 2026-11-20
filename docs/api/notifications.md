# API — Notificações

Notificações in-app do utilizador: listagem, feed, marcar como lida, arquivar e apagar (`Modules/Notification`). Convenções gerais (envelope, paginação, códigos de estado): ver [README.md](README.md). Arquitetura: [../architecture/cross-cutting-services.md](../architecture/cross-cutting-services.md) e [../architecture/modules/user-friends-notification.md](../architecture/modules/user-friends-notification.md#notification-modulesnotification).

Todas as rotas usam só `auth:sanctum` (**sem** `setlocale`). O idioma dos textos vem diretamente de `user_preferences.lang`.

Código: `Http/Controllers/Api/V1/NotificationController.php`, `Actions/Notifications/*`, `Services/NotificationCacheService.php`, `Paginates/NotificationPaginate.php`, `Http/Resources/NotificationResource.php`.

## Como uma notificação é montada

Na BD, uma notificação guarda só o `type_code` e um `data` JSON com variáveis. Os textos vêm do tipo (`notification_types`) e são montados no pedido por `RenderNotificationTemplateAction`:

1. `title` e `message` = `notification_types.title[lang]` / `message[lang]`, com `lang = user_preferences.lang`;
2. `pathname` = `notification_types.pathname` (caminho do frontend para onde a notificação leva);
3. cada keyword associada ao tipo (`notification_type_keywords` → `notification_keywords.keyword`, ex.: `[$username]`) é substituída em title, message, pathname e campos de email por `data[<keyword sem [$ ]>]`.

**Objeto `notification` (`NotificationResource`)**, usado no feed e em `read`:

```json
{
  "id": 128,
  "title": "Novo pedido de amizade",
  "message": "anasilva enviou-te um pedido de amizade.",
  "pathname": "/friends?tab=received",
  "createdAt": "2026-11-18 10:40:00",
  "readAt": null
}
```

| Campo | Descrição |
|---|---|
| `id` | Id da notificação (ou do broadcast, no feed) |
| `title`, `message` | Já renderizados no idioma do utilizador. Podem conter HTML se o tipo tiver sido escrito com HTML |
| `pathname` | Caminho do frontend, com keywords substituídas. Pode ser `null` se o tipo não tiver pathname |
| `createdAt` | `Y-m-d H:i:s` |
| `readAt` | Data ISO 8601 (`2026-11-18T10:41:12.000000Z`) ou `null` |

(Os textos de exemplo acima são ilustrativos: os textos reais são configurados no backoffice.)

**Requisitos para o render não falhar** (senão o pedido devolve 500):
- o tipo tem de ter `title`, `message`, `mail_subject`, `mail_message` e `mail_signature` preenchidos com a chave da língua do utilizador. O render lê sempre os cinco, e um acesso `null[lang]` gera um warning que o Laravel converte em `ErrorException`. Se o tipo tiver keywords, `pathname` também não pode ser `null`;
- `data` tem de ter uma chave para cada keyword do tipo.

## Tipos enviados atualmente

Só o módulo Friends cria notificações por código (`FriendshipRequestRepository`):

| `type_code` | Quando | Destinatário | `data` |
|---|---|---|---|
| `FRIEND_REQUEST_RECEIVED` | `POST friendship-requests/{id}/send` | quem recebe o pedido | `{"username": "<username de quem enviou>"}` |
| `FRIEND_REQUEST_ACCEPTED` | `POST friendship-requests/{id}/accept` | quem tinha enviado o pedido | `{"username": "<username de quem aceitou>"}` |
| `FRIEND_REQUEST_DECLINED` | `DELETE friendship-requests/{id}/decline` | quem tinha enviado o pedido | `{"username": "<username de quem recusou>"}` |

- Estes três tipos **não são semeados** por nenhuma migration. Têm de existir em `notification_types` (criados no backoffice, com a keyword `[$username]`). Se faltarem, a FK `notifications.type_code` falha e a ação de amizade devolve 500, com rollback.
- A migration semeia apenas o tipo `test` (keywords `[$amount]`, `[$description]`), que nenhum código envia.
- Os **broadcasts** (`broadcast_notifications`) são criados no backoffice e cada um tem um tipo próprio (`is_broadcast = true`). São globais: não têm `user_id`, `data` nem `read_at`.
- Convites de contas, dívidas e metas **não** geram notificações.

---

### `GET /api/v1/notifications`

Usa o [contrato comum de paginação, filtros e pesquisa](../architecture/module-listings.md). A resposta contém `success`, `message`, `data`, `pagination`, `errors` e `meta` quando existirem extras. Parâmetros inválidos devolvem 422.

`NotificationPaginate` une notificações pessoais não arquivadas/não apagadas e broadcasts, com seleção explícita e equivalente de colunas. Ordem por created_at desc, desempate por source/id; source distingue personal/broadcast quando IDs colidem. `meta.count` fornece não lidas segundo NotificationCacheService.

`search` procura título/mensagem guardados no template, sem substituir keywords para efeitos da pesquisa. `order_by=created_at` é a whitelist. Linhas de NotificationResource incluem id/source, title, message, pathname, createdAt e readAt. O feed auxiliar mantém o seu contrato próprio; o frontend paginado usa esta listagem em todas as páginas.

---

### `GET /api/v1/notifications/feed`

As 20 notificações mais recentes (não arquivadas) do utilizador, juntas com os 20 broadcasts mais recentes, cortadas a 20 e ordenadas por `created_at desc`. Também devolve o número de não lidas. Os dois valores são cacheados durante 5 min (`notifications:feed:{userId}`, `notifications:unread:{userId}`). A cache é invalidada ao criar, ler, arquivar ou apagar.

**Autenticação/Permissão:** token.

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": [
    {
      "id": 128,
      "title": "Novo pedido de amizade",
      "message": "anasilva enviou-te um pedido de amizade.",
      "pathname": "/friends?tab=received",
      "createdAt": "2026-11-18 10:40:00",
      "readAt": null
    },
    {
      "id": 127,
      "title": "Pedido de amizade aceite",
      "message": "rcosta aceitou o teu pedido de amizade.",
      "pathname": "/friends",
      "createdAt": "2026-11-17 21:03:10",
      "readAt": "2026-11-17T21:05:00.000000Z"
    }
  ],
  "errors": null,
  "meta": { "count": 1 }
}
```

- `meta.count` = notificações do utilizador com `read_at IS NULL`. Inclui as **arquivadas** (a contagem não filtra `archived_at`) e exclui os broadcasts.
- Os ids das notificações e dos broadcasts são de tabelas diferentes, e o `merge` de coleções Eloquent indexa por chave primária. Se um broadcast tiver o mesmo id de uma notificação, uma delas desaparece do feed.

**Erros:**
- `401` — sem token.
- `500` — erro inesperado. **Bug provável:** os broadcasts não têm `data`, e `RenderNotificationTemplateAction::execute(…, array $notificationData, …)` recebe `null` → `TypeError`. O feed falha sempre que um broadcast entra nos 20 mais recentes.

---

### `POST /api/v1/notifications/{id}/read`

Marca a notificação como lida (`read_at = now()`). Se já estiver lida, não muda nada e devolve-a na mesma.

**Autenticação/Permissão:** token. **Não verifica se a notificação pertence ao utilizador** (ver nota de segurança no fim).

**Parâmetros de rota:**

| Nome | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | int | sim | Id da notificação (tabela `notifications`; broadcasts não são suportados) |

**Body:** nenhum.

**Resposta 200:**

```json
{
  "success": true,
  "message": "",
  "data": {
    "id": 128,
    "title": "Novo pedido de amizade",
    "message": "anasilva enviou-te um pedido de amizade.",
    "pathname": "/friends?tab=received",
    "createdAt": "2026-11-18 10:40:00",
    "readAt": "2026-11-18T10:41:12.000000Z"
  },
  "errors": null
}
```

**Erros:**
- `401` — sem token.
- `500` — id inexistente ou apagado. O `findOrFail` lança `ModelNotFoundException`, mas o `safe()` apanha-a como `Throwable` e devolve 500 (não 404), com `message: "No query results for model [...]"`.

---

### `POST /api/v1/notifications/read-all`

Marca como lidas todas as notificações não lidas do utilizador (incluindo as arquivadas). Os broadcasts não têm estado de leitura.

**Autenticação/Permissão:** token.

**Body:** nenhum.

**Resposta 200:**

```json
{ "success": true, "message": "", "data": null, "errors": null }
```

**Erros:** `401`, `500`.

---

### `POST /api/v1/notifications/{id}/archive`

Arquiva a notificação (`archived_at = now()`). Deixa de aparecer na listagem e no feed. Se já estiver arquivada, não faz nada.

**Autenticação/Permissão:** token. **Não verifica o dono.**

**Parâmetros de rota:** `id` (int, obrigatório) — id da notificação.

**Body:** nenhum.

**Resposta 200:**

```json
{ "success": true, "message": "", "data": null, "errors": null }
```

**Erros:** `401`. `500` se o id não existir (como em `read`).

---

### `DELETE /api/v1/notifications/{id}`

Apaga a notificação (soft delete, `deleted_at`).

**Autenticação/Permissão:** token. **Não verifica o dono.**

**Parâmetros de rota:** `id` (int, obrigatório) — id da notificação.

**Resposta 200:**

```json
{ "success": true, "message": "", "data": null, "errors": null }
```

**Erros:** `401`. `500` se o id não existir ou já estiver apagado.

---

## Nota de segurança

`read`, `archive` e `destroy` usam `NotificationRepository::show($id)` (`findOrFail` sem filtro por `user_id`). Qualquer utilizador autenticado pode ler (a resposta de `read` devolve o conteúdo renderizado), arquivar ou apagar notificações de outro utilizador conhecendo o id (IDOR). A correção é filtrar por `user_id = Auth::id()` no repository ou nas actions.
