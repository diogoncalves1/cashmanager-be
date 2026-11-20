# Arquitetura — autenticação, permissões e partilha

## Autenticação (`app/Http/Controllers/Api/V1/AuthController.php`, `routes/api.php`)

| Método | Rota `/api/v1/…` | Auth | Notas |
|---|---|---|---|
| POST | `register` | — | `UserRepository::store`. O `UserObserver` cria `user_preferences` e dispara `VerifyEmail` |
| POST | `login` | — | Devolve `{user, token}` (201), token Sanctum `web-token`. Credenciais inválidas: 422; email por verificar: 403 |
| POST | `reset-password` | — | Dispara `ResetPassword` (email com link `/change-password?token=…`) |
| POST | `reset-change-password` | sanctum | Usa o token do link |
| POST | `change-password` | sanctum | Valida a password atual (403 se errada) |
| POST | `verify-email` | sanctum | Usa o token do link (`FRONTEND_URL/verify-email?token=…`). Dispara `Verified` e `EmailVerified` |
| POST | `logout` | sanctum | Apaga o token atual |
| GET | `me` | sanctum | `UserResource` |

O frontend envia `Authorization: Bearer <token>`. O backoffice `/admin` usa sessão web (Breeze, `routes/auth.php`).

## Estado local das policies de contas (20/11/2026)

O repository passou a chamar Gate para show/update/status/destroy. `AccountPolicy::show` aceita membership; `update` aceita creator ou editAccount; a policy define `delete`, mas o repository chama `destroy`. As flags da lista continuam a usar viewAccount/editAccount/destroyAccount. Isto não representa uma regra uniforme entre lista, detalhe, resumo e mutação. Erros de autorização lançados dentro dos catches genéricos também podem virar 500. Ver [problemas conhecidos](known-issues.md) e [contrato de contas](../api/accounts.md).

`IsShareable::userSharedRole` aceita um id de utilizador ou um User com pivot já carregado; sem utilizador devolve null. Confirmar sempre que o pivot pertence ao recurso e ao membro em questão.

## Os dois sistemas de permissões

### 1. Global (RBAC do admin): módulo `Permission`

- Tabelas `roles`, `permissions`, `role_permissions`, `role_user`.
- `Gate::define('authorization', fn($user, $permission))` em `Modules/Permission/Providers/PermissionServiceProvider.php`. Sem argumento, usa a chave `permission` da action da rota.
- Usado em: middleware `admin` (permissão `viewAdmin`), `AppController::allowedAction()`, `@can` no Blade, e no backoffice de Category, Currency, Asset, Language e Users.
- Roles semeados: `superAdmin` (id 1, todas as permissões) e `admin`. O utilizador 1 é superAdmin.

### 2. Por recurso partilhado: módulo `SharedRoles`

- Tabelas `shared_roles` (code, name JSON `{en,pt}`, visible), `shared_permissions`, `shared_permission_roles`.
- O role `creator` (id 1, invisível) tem todas as permissões partilhadas. Os outros roles criam-se no backoffice. `GET /api/v1/shared-roles` devolve todos exceto o creator.
- Recursos partilháveis usam `Modules/SharedRoles/Traits/IsShareable.php`: `Account`, `Debt` e `FinancialGoal`. O pivot de membros (`account_users`, `debts_user`, `financial_goal_users`) guarda `shared_role_id`.
- Verificação típica no repository:
  ```php
  $role = $this->userSharedRole($account, Auth::id());   // null = não é membro
  if (! $role || ! $role->hasPermission('editAccount')) {
      throw new UnauthorizedUpdateAccountException();
  }
  ```
- `Modules/SharedRoles/Services/CheckPermission::check()` é a versão com cache (5 min, chave `shared_role:{id}:permission:{code}`). É usada em policies e Resources.
- Lista completa dos códigos: [database/permissions.md](database/permissions.md).

**Atenção:**
- As listagens financeiras usam EXISTS com pertença e permissão do mesmo membro; convites e social aplicam o âmbito próprio. Ver [ModulePaginate](module-listings.md).
- As policies AccountPolicy e RecurringTransactionPolicy estão registadas e são usadas nos respetivos fluxos; consultar cada método efetivamente chamado.
- O role `creator` está hard-coded por código em vários repositories.

## Fluxo de partilha e convites

Accounts, Debts e FinancialGoal seguem o mesmo fluxo, com `{recurso}` = `accounts` | `debts` | `financial-goals`.

1. **Convidar** — `POST {recurso}/{id}/invite/{userId}` com `shared_role_id`. As verificações correm por esta ordem:
   1. o role não é creator (`CreatorInviteException`);
   2. são amigos (`FriendshipRepository::areFriends`);
   3. não é o próprio (`SelfInviteException`);
   4. menos de 3 convites `revoked` em 30 dias (`InvitesLimitExceededException`);
   5. sem convite pendente (`InviteAlreadySentException`);
   6. ainda não é membro (`AlreadyRelationException`);
   7. quem convida tem `manageAccountUsers` | `manageDebtUsers` | `manageFinancialGoalUsers` (`InviteUserNotAllowedException`).
2. **Convidado:**
   - `POST {id}/accept` cria a linha de membro com o role do convite e põe o estado `accepted`;
   - `POST {id}/revoke` recusa o convite, que passa a `revoked`.
3. **Gestor:**
   - `DELETE {id}/invite/{userId}` cancela um convite pendente;
   - `POST {id}/revoke-user/{userId}` remove um membro;
   - `PUT {id}/user-role/{userId}` muda o role de um membro.
4. **Membro:** `DELETE {id}/leave`. O creator não pode sair (`CreatorCantLeaveException`).
5. **Invariantes:**
   - há um único creator e ninguém recebe esse role;
   - ninguém altera o próprio role, nem o de quem tem o mesmo role;
   - o creator não pode ser removido.
6. **Códigos HTTP reais:** as exceções de SharedRoles usam **401** para falta de permissão e **500** para regras de negócio (não 403/422). Detalhe por endpoint em [../api/sharing.md](../api/sharing.md).
7. **Estatísticas e listas de convites:**
   - `GET {recurso}/invitations-stats`;
   - `GET {recurso}/sent-invitations`;
   - `GET {recurso}/received-invitations`.
8. **Activity Log:** cada passo regista um evento (`user_invited`, `user_joined`, `invited_destroyed`, `invited_revoked`, `user_revoked`, `user_role_updated`, `user_leaved`). Os convites **não** geram notificações.

Tabelas de convites: `account_user_invites`, `debt_user_invites`, `financial_goal_user_invites`, com `status` ∈ `pending | revoked | accepted` e `invited_by_id`.
