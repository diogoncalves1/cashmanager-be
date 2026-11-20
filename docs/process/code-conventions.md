# Convenções de código — CashManager (backend)

Regras tiradas do código existente. Código novo deve segui-las. Se o código antigo divergir, prevalece esta norma, mas só se reescreve código antigo quando a tarefa o justificar.

## 1. Geral

- PHP **8.2** e Laravel **12**.
- **Formatação:** `vendor/bin/pint` (preset Laravel). `.editorconfig`: 4 espaços, LF, UTF-8, newline final.
- **Idioma:**
  - código, nomes e comentários em **inglês**;
  - mensagens para o utilizador sempre através de traduções (`pt` e `en`), nunca texto fixo.
- **Tipos:** declarar tipos de parâmetros e de retorno (`: JsonResponse`, `: array`, `?SharedRole`).
- **Injeção:** dependências por injeção no construtor (`private AccountRepository $repository`). Evitar `new Repository()` e `app()` dentro da lógica, exceto em actions pontuais.
- **Âmbito:** cada alteração fica dentro do módulo a que pertence. Código partilhado só vai para `app/` se for mesmo transversal.

## 2. Estrutura e nomes

| Elemento | Local | Nome |
|---|---|---|
| Controller API | `Modules/<M>/Http/Controllers/Api/V1/` | `<Entity>Controller` |
| Controller backoffice | `Modules/<M>/Http/Controllers/` ou `Admin/` | `<Entity>Controller` |
| Validação | `Http/Requests/` | `<Entity>Request`; `Store<Entity>Request`, `Update<Entity>Request` quando diferem |
| Resposta | `Http/Resources/` | `<Entity>Resource`, `<Entity>Collection` |
| Lógica de negócio | `Repositories/` | `<Entity>Repository` |
| Lógica complexa e transversal | `Services/` | nome do conceito (`GoalReservations`, `PersonalCategoryService`) |
| Operação única | `Actions/` | `<Verb><Object>Action` com `execute()` |
| Listagem API | `Paginates/` | `<Entity>Paginate extends ModulePaginate` |
| Filtros / pesquisa API | `Filters/`, `Searches/` | `<Entity>Filter`, `<Entity>Search` |
| Listagem backoffice | `DataTables/` | `<Entity>DataTable` (Yajra) |
| Modelo | `Entities/` | singular, `<Entity>` |
| Modelo sobre view SQL | `Entities/` | `<Entity>View` / `<Entity>BasicView` |
| Exceção | `Exceptions/[<Sub>/]` | `<Condição>Exception` (`DebtNotPendingException`) |
| Regra de validação | `Rules/` | nome da regra (`SelectableCategory`) |
| Comando | `Console/Commands/` | assinatura `<domínio>:<ação>` (`currency:fetch-daily`) |
| Job | `Jobs/` | `<Verb><Object>Job` |
| Evento / listener | `Events/`, `Listeners/` | `<Fact>` / `<Fact>Listener` |

## 3. Rotas

- Rotas API ficam em `Modules/<M>/Routes/api.php`, dentro de `Route::prefix('v1')->middleware(['auth:sanctum', 'setlocale'])`.
- URLs em **kebab-case, no plural**: `financial-goals`, `debt-payments`, `friendship-requests`.
- Parâmetros `{id}` e `{userId}`.
- Ações que não são CRUD usam `POST` com um verbo: `{id}/confirm`, `{id}/mark-paid`, `{id}/archive`.
- Rotas fixas (`accounts/all`, `debts/stats`) declaram-se **antes** do `apiResource` e de `{id}`.
- Rotas de backoffice ficam em `Routes/web.php` com prefixo `admin` e middleware `auth` + `admin`.

## 4. Controllers

- Estendem `App\Http\Controllers\ApiController`.
- São finos: recebem o pedido, chamam o repository e devolvem `ok()`/`fail()`.
- Envolvem a chamada em `try/catch (\Exception $e)`, fazem `Log::error($e)` e `return $this->fail($e->getMessage(), $e, $e->getCode())`. Em alternativa, `$this->safe(fn () => ...)`.
- A mensagem de sucesso é traduzida: `__('accounts::messages.accounts.store', ['name' => $account->name])`.
- Dados extra vão em `additionals` (acabam em `meta`). Não se altera a forma do envelope.

## 5. Validação (FormRequest)

- `authorize()` devolve `true`. A autorização faz-se no repository.
- Para regras de categoria usa-se o trait `ValidatesCategorySelection` / a regra `SelectableCategory`.
- Campos imutáveis num update não entram nas regras de update. O repository também os ignora.
- Pedidos de módulos com frontend que depende de mensagens por campo devem devolver sempre JSON 422 (ver `RecurringTransactionRequest`).

## 6. Repositories e regras de negócio

- **Autorização:**
  - shared role obtido com `userSharedRole($model, $userId)`;
  - permissão verificada com `$role->hasPermission('code')`, ou `CheckPermission::check()` em listagens.
  - Sem role → tratar como sem permissão.
- **Escritas:**
  - correm dentro de `DB::transaction`;
  - bloqueiam com `lockForUpdate()` pela ordem **user → account → recurso** (para evitar deadlocks).
- **Totais guardados:**
  - `balance`, `paid_amount` e `contributed_amount` só mudam através dos métodos próprios (`adjustBalance`, `sync`…);
  - nunca com `update()` solto.
- **Chamadas internas:** reutiliza-se o repository de outro módulo (por exemplo `TransactionRepository::store`), com `$checkPermission = false` quando a verificação já foi feita. Nunca se fazem pedidos HTTP internos.
- **Activity log:** toda a mutação de um recurso partilhado regista `ActivityLogRepository::storeActivity(...)` com `metadata.type` em snake_case (`payment_confirmed`). Tem de existir a tradução correspondente em `activitylog::messages`.
- **Visibilidade:** filtrar sempre pelo utilizador autenticado (membership e permissão). Nunca confiar num `user_id` vindo do pedido.

## 7. Exceções

- **Uma classe por condição de negócio:**
  - mensagem traduzida em `<modulo>::exceptions.*`;
  - `protected $code` com o status HTTP correto: **403** permissão, **404** não encontrado, **422** regra de negócio ou estado inválido.
  - Não usar 500 para erros previsíveis.
- **Erros de validação de negócio com campo associado:** `ValidationException::withMessages([...])`.

## 8. Base de dados

- **Tabelas:**
  - `snake_case` no plural (`debt_payments`);
  - pivots de membros `<recurso>_users`, convites `<recurso>_user_invites`;
  - views `<nome>_view`.
- **Colunas:**
  - `snake_case`;
  - montantes `decimal(15,2)`, câmbios `decimal(15,4)`;
  - estados como `enum` ou `string` com valores em inglês (`pending`, `completed`).
- **FKs:** `cascade` ou `set null` de forma explícita.
- **Views SQL:** devolvem colunas em `camelCase` (`totalPayments`, `userIds`). Alteram-se com uma migration nova (`DROP VIEW IF EXISTS` + `CREATE VIEW`). Devem ser compatíveis com `ONLY_FULL_GROUP_BY`.
- **Seeds em migrations:** permissões e dados obrigatórios inserem-se na migration que cria a tabela, ligados ao role 1.
- **Migrations:**
  - têm `down()` funcional, exceto migrações de dados irreversíveis, que devem estar documentadas;
  - nunca se edita uma migration já executada em produção — cria-se outra.

## 9. Respostas JSON (Resources)

- Campos em **camelCase** (`currencyId`, `totalAmount`, `nextOccurrence`).
- Montantes como número (`(float)`). As versões formatadas usam o sufixo `Formated`, que é a grafia já usada pelo frontend: não corrigir só num sítio.
- Nomes traduzíveis (categorias, moedas) já resolvidos no idioma do utilizador.
- Linhas de listagem incluem `actions` (booleanos por ação), calculadas a partir das permissões.

## 10. Permissões

- **Formato do código:** `camelCase` com verbo + entidade: `viewAccount`, `storeDebtPayment`, `manageFinancialGoalUsers`.
- Cada permissão nova é semeada numa migration e atribuída ao role `creator` (partilhada) ou `superAdmin` (global).

## 11. Traduções

- Ficam em `Modules/<M>/Resources/lang/{pt,en}/{messages,attributes,exceptions,validation}.php`.
- Chaves em `camelCase` ou `kebab-case`, conforme o ficheiro existente. Os placeholders são iguais nos dois idiomas.
- O `TranslationCatalogueTest` tem de passar.

## 12. Testes

- **Feature tests:** `Modules/<Module>/Tests/Feature/<Funcionalidade>Test.php`, para endpoints e fluxos.
- **Unit tests de domínio:** `Modules/<Module>/Tests/Unit/`, para cálculos e apresentação.
- Cada correção de bug traz um teste de regressão quando o risco o justifica: saldos, permissões, migrações de dados.
- Os testes correm em SQLite em memória. Se o código depender de views ou de funções MySQL, o teste prepara o necessário ou documenta a limitação.

## 13. Documentação

- Uma alteração de rota, de body ou de resposta atualiza `Back/CashManager/docs/api/<domínio>.md`.
- Uma alteração de regra de negócio ou de tabela atualiza `Back/CashManager/docs/architecture/`.
- Notas curtas e operacionais vão para o `AGENTS.md`.

Testes transversais ficam em `tests/`. Registar cada suite de módulo em `phpunit.xml`. Consultar [arquitetura de listagens](../architecture/module-listings.md).
