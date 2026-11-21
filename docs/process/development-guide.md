# Guia de desenvolvimento

## Antes de começar uma tarefa

1. Ler `Back/CashManager/AGENTS.md` e o [índice das docs](../README.md).
2. Abrir o ficheiro de [módulo](../architecture/modules/) relevante e seguir as dependências do fluxo. Uma auditoria abrangente pode exigir análise transversal.
3. Correr `git status`. Pode haver trabalho do utilizador por commitar; não o reverter.

## Checklist para um endpoint novo ou alterado

Percorrer, no módulo certo, esta cadeia:

`Routes/api.php` → `Http/Controllers/Api/V1/*Controller` → `Http/Requests/*Request` → `Repositories/*Repository` (ou `Services/`) → `Entities/*` (e views `*View`) → `Database/Migrations` → `Http/Resources/*` → `Resources/lang/{pt,en}`

- **Rota:** fica dentro de `Route::prefix('v1')->middleware(['auth:sanctum','setlocale'])`. Rotas fixas (`accounts/all`) vêm **antes** de `apiResource` e de `{id}`.
- **Controller:**
  - estende `ApiController`;
  - devolve `ok()`/`fail()` com mensagem traduzida `__('modulo::messages.…')`;
  - não contém lógica de negócio.
- **Request:** só valida. Para categorias, usar o trait `ValidatesCategorySelection` / a regra `SelectableCategory`.
- **Repository:**
  - verificar a policy/Gate ou shared role realmente usados e alinhar a query de visibilidade com a ação;
  - fazer a escrita em `DB::transaction` com `lockForUpdate` (ordem: user → account → recurso);
  - registar no Activity Log quando o recurso é partilhável.
- **Exceção de domínio:** fica em `Exceptions/`, com mensagem traduzida e `$code` = status HTTP correto (403/404/422, não 500).
- **Permissão partilhada nova:** criar uma migration que a insere e a liga ao role `creator` (id 1). Ver [permissoes.md](../architecture/database/permissions.md).
- **Traduções:** adicionar a chave em `pt` **e** `en`. O `TranslationCatalogueTest` falha se faltar uma.
- **Views SQL:** alteram-se com uma migration nova (`DROP VIEW IF EXISTS` + `CREATE VIEW`). Se a view entra numa DataTable, garantir compatibilidade com `ONLY_FULL_GROUP_BY` (preferir `whereExists` a joins com views agregadas).

## Regras que não se podem partir

- O saldo da conta só muda através de `TransactionRepository` (adjust/update/reverse). Só as transações `completed` contam.
- Não há transferências; uma "transferência" seriam duas transações.
- Os pagamentos de dívida criam sempre uma despesa. Nas metas, contribution/withdrawal **não** criam transação (são reservas).
- Moedas diferentes não se somam sem conversão. Os endpoints "resume"/"summary" mantêm totais por moeda.
- O dashboard reutiliza repositories. Nunca faz pedidos HTTP internos nem devolve listas completas de DataTable.
- O catálogo de ícones `CategoryIcons::ALL` tem de bater certo com o frontend.
- **Budgets** é só planeamento (`Front/CashManager/BUDGETS*.md`). Não implementar sem pedido explícito.

## Criar um módulo novo

```bash
php artisan module:make NomeModulo
```

Depois:
1. Confirmar que ficou `true` em `modules_statuses.json`.
2. Seguir a estrutura descrita em [layers-and-modules](../architecture/layers-and-modules.md#anatomia-de-um-módulo-nwidartlaravel-modules).
3. Registar traduções, policies e agendamentos no `ServiceProvider` do módulo.

## Testes

Comandos, matriz de cobertura e limites SQLite/MySQL estão em [testing.md](testing.md). Escolher a verificação pelo fluxo afetado; uma listagem de rotas não exercita o endpoint.

## Formatação, commits e branches

- `vendor/bin/pint` para PHP. Restantes regras em [code-conventions.md](code-conventions.md).
- Branches, commits, push e PRs: [git-workflow.md](git-workflow.md).

## Manter a documentação

Quando mudar um fluxo de negócio, uma rota ou uma tabela:
1. Atualizar o ficheiro do módulo em `Back/CashManager/docs/architecture/modules/` e o ficheiro da API em `Back/CashManager/docs/api/`; e a arquitetura ou a BD se for o caso.
2. Acrescentar uma nota curta a `AGENTS.md` se for algo que uma sessão futura precisa de saber logo. Seguir [manutenção e fontes](documentation.md), incluindo o versionamento de `docs/` com o código.
3. Corrigir ou remover a entrada em [known-issues.md](../architecture/known-issues.md) quando um bug for resolvido.
