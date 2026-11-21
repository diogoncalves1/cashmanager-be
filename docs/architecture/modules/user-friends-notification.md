# Módulos User, UserPreferences, Friends, Notification, EmailLayout

## User (`Modules/User`)

- Tabela `users`: `name`, `email` (único), `email_verified_at`, `username` (único, nullable), `username_updated_at`, `password`. A migration cria também `password_reset_tokens` e `sessions`, e semeia o superadmin.
- `Entities/User.php` (HasApiTokens, Notifiable): `roles()`, `preferences()`, `notificationPreferences()`, `getBalance()` (soma convertida das contas com `updateUserBalance`).
- Rotas API (`/api/v1`):
  - `PUT me` → `Api\UserController@updateSettings` (nome, email, username, preferências currency_id/lang);
  - `GET users/search` → `SearchUserPaginate` (todos exceto o próprio, com `status` friend/pending/blocked);
  - `GET check-username` (público).
- Admin: `/admin/users` (CRUD + gestão de roles).
- Eventos de email: ver [cross-cutting-services](../cross-cutting-services.md#emails-transacionais-modulesuser--modulesemaillayout).

## UserPreferences (`Modules/UserPreferences`)

- Tabela `user_preferences`: `user_id` (PK no modelo), `currency_id`, `lang` (FK `languages.code`).
- Modelo `Entities/UserPrefence.php` (nome com gralha — manter).
- `PUT /api/v1/user-preferences` (`lang` ∈ pt,en; `currency_id` existente).
- Middleware `setlocale` (alias registado no service provider).
- `Observers/UserObserver.php` (User::created): dispara `VerifyEmail` e cria preferências `currency_id=1`, `lang='en'`.

## Friends (`Modules/Friends`)

- Tabelas: `friendships(sender_id, receiver_id, status friend|blocked)`, `friendship_requests(sender_id, receiver_id, status pending|declined)`.
- Regras (`Repositories/FriendshipRequestRepository.php`, `FriendshipRepository.php`):
  - **enviar**: user existe, não é o próprio, não são já amigos, sem pedido pendente em nenhum sentido, nenhum bloqueado, limite de 3 recusas em 30 dias;
  - **aceitar**: o remetente não pode aceitar; cria `friend` e apaga o pedido;
  - **recusar** → `declined`; **cancelar** → apaga;
  - **bloquear**: apaga amizade e pedidos e cria `blocked` com o bloqueador como sender; só ele desbloqueia;
  - **remover**: falha se bloqueados.
- Notificações: `FRIEND_REQUEST_RECEIVED`, `FRIEND_REQUEST_ACCEPTED`, `FRIEND_REQUEST_DECLINED` (data `{username}`).
- `FriendshipRepository::areFriends` é usado pelos convites de contas/dívidas/metas.
- Rotas (`/api/v1`, sanctum + setlocale; `{id}` = id do outro utilizador):
  - `friendships`: `GET /`, `GET blocked`, `GET stats`, `DELETE {id}/remove`, `POST {id}/block`, `POST {id}/unblock`;
  - `friendship-requests`: `POST {id}/send`, `POST {id}/accept`, `DELETE {id}/decline`, `DELETE {id}/cancel`, `GET sent`, `GET received`.
- `Traits/Friendable.php`: não usado e partido — não reutilizar.

## Notification (`Modules/Notification`)

Arquitetura descrita em [cross-cutting-services](../cross-cutting-services.md#notificações-in-app-modulesnotification).

- Tabelas: `notification_types`, `notification_keywords`, `notification_type_keywords`, `notifications` (`user_id`, `type_code`, `data` JSON, `read_at`, `archived_at`, soft deletes), `user_notification_preferences`, `broadcast_notifications`.
- Ações (`Actions/`): `SendNotificationAction`, `MarkAsReadAction`, `MarkAllAsReadAction`, `ArchiveNotificationAction`, `DeleteNotificationAction`, `RenderNotificationTemplateAction` — todas invalidam a cache.
- Rotas API (`/api/v1/notifications`, só `auth:sanctum`): `GET /` (ModulePaginate), `GET feed` (`meta.count` = não lidas), `POST {id}/archive`, `POST {id}/read`, `POST read-all`, `DELETE {id}`.
- Admin (`auth` + `admin`): broadcasts, keywords, tipos e `notification-types/{id}/manage`.

## EmailLayout (`Modules/EmailLayout`)

- Tabelas: `email_types(name, code, description)`, `email_type_keywords`, `email_layouts(subject, email, signature, attach, attach_name, email_type_id)`.
- `EmailLayoutRepository::showByEmailTypeCode($code)` → primeiro layout do tipo.
- Só rotas web de admin (`/admin/settings/emailLayouts`, `/admin/emailTypes`).
- Códigos obrigatórios na BD: `verify_email`, `email_verified`, `reset_password`, `password_changed`.
