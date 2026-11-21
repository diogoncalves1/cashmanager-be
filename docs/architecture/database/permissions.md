# Base de dados — permissões semeadas

## Permissões partilhadas (`shared_permissions`)

Todas ficam atribuídas ao role `creator` (`shared_role_id = 1`). São semeadas pela migration da tabela do recurso.

| Recurso | Códigos |
|---|---|
| Contas | `viewAccount`, `editAccount`, `destroyAccount`, `manageAccountUsers`, `updateUserBalance` |
| Transações | `viewTransaction`, `createTransaction`, `editTransaction`, `destroyTransaction`, `confirmTransaction` |
| Dívidas | `viewDebt`, `editDebt`, `destroyDebt`, `manageDebtUsers` |
| Pagamentos de dívida | `viewDebtPayment`, `storeDebtPayment`, `editDebtPayment`, `destroyDebtPayment`, `confirmDebtPayment` |
| Metas | `viewFinancialGoal`, `updateFinancialGoal`, `destroyFinancialGoal`, `manageFinancialGoal`, `manageFinancialGoalUsers` |
| Movimentos de metas | `viewFinancialGoalTransaction`, `storeFinancialGoalTransaction`, `updateFinancialGoalTransaction`, `confirmScheduledFinancialGoalTransactions`, `destroyFinancialGoalTransaction` |
| Recorrentes | `editRecurringTransaction`, `destroyRecurringTransaction` |

`updateUserBalance` decide se a conta entra no património e nos totais do utilizador (`getStats`, `User::getBalance`).

## Permissões globais (`permissions`)

Ficam atribuídas ao role `superAdmin` (id 1). O utilizador 1 é superAdmin.

| Área | Códigos |
|---|---|
| Base | `superAdmin`, `viewRole`, `createRole`, `editRole`, `destroyRole`, `manageRole` |
| Utilizadores | `viewUser`, `createUser`, `editUser`, `destroyUser`, `manageUserRoles` |
| Categorias por defeito | `viewCategoryDefault`, `createCategoryDefault`, `editCategoryDefault`, `destroyCategoryDefault` |
| Moedas | `viewCurrency`, `createCurrency`, `editCurrency`, `destroyCurrency`, `updateRates` |
| Ativos | `viewAsset`, `createAsset`, `editAsset`, `destroyAsset` |
| Shared roles | `viewSharedRole`, `createSharedRole`, `editSharedRole`, `destroySharedRole`, `manageSharedRolePermission`, `viewSharedPermission`, `createSharedPermission`, `editSharedPermission`, `destroySharedPermission` |

⚠️ `viewAdmin` (usada pelo middleware `admin`) **não é semeada** em nenhuma migration — tem de ser criada manualmente para o backoffice funcionar.

Para adicionar uma permissão nova:
1. Criar uma migration que a insere.
2. Na mesma migration, ligá-la ao role 1 (`creator` ou `superAdmin`, conforme o tipo).
3. Usá-la no repository.
