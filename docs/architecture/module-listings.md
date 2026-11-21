# Listagens da API: ModulePaginate, ModuleFilter e ModuleSearch

Arquitetura adotada em 20/11/2026. Aplica-se a todas as listagens paginadas da API. As classes Yajra DataTables ficam exclusivamente no backoffice web; não se usam como paginadores, filtros ou serializadores da API.

## Responsabilidades e regras

1. O controller recebe o Request e injeta o paginator da entidade. Mantém autorização do recurso pai (atividade, por exemplo), chama `respondModulePaginated` e fornece extras em `meta`.
2. `App\Paginates\ModulePaginate` centraliza validação, aplicação de filtros/pesquisa, ordenação e `LengthAwarePaginator`. O método `paginateByUser(User, Request)` recebe o utilizador autenticado explicitamente.
3. Cada entidade tem `<Entity>Paginate` em `Modules/<Module>/Paginates/`, com `query(User, Request)`, `RESOURCE`, mapa de campos de ordenação e dependências Filter/Search no construtor. Ex.: `AccountPaginate` e `TransactionPaginate` pertencem ambos a Accounts.
4. `query` aplica o âmbito de visibilidade **antes** dos filtros e da pesquisa e carrega as relações/contagens usadas pelo Resource. IDs recebidos do cliente apenas restringem esse âmbito; nunca escolhem a identidade autenticada.
5. `App\Filters\ModuleFilter` aplica condições AND sobre campos/operadores declarados por `<Entity>Filter`, em `Filters/`. O mapa `translateFields` traduz nomes públicos para colunas SQL fixas. Nunca se altera o nome do campo entre operadores do mesmo intervalo.
6. `App\Searches\ModuleSearch` aplica uma pesquisa textual agrupada com OR sobre campos e relações declarados por `<Entity>Search`, em `Searches/`. Esse grupo é combinado por AND com pertença, permissões e filtros. Não escolhe colunas a partir do pedido.
7. Resources em `Http/Resources/` apresentam as linhas, traduções e `actions`; não constroem paginação nem aplicam o âmbito. Campos pessoais devem ser explicitamente limitados: descoberta de utilizadores e listas sociais usam dados públicos.
8. `ApiResponse::respondPaginated` aceita uma ResourceCollection ou uma classe JsonResource e devolve o contrato comum. `safe()` relança validação, autorização, HTTP e model-not-found para preservar os respetivos códigos.
9. Contas, transações, dívidas, pagamentos e metas usam EXISTS para combinar o utilizador e a permissão **na mesma membership**. Não se juntam views agregadas ao count dos movimentos das metas.
10. Estatísticas e pickers reutilizam `filteredQuery` quando devem acompanhar a listagem. Nenhum consumidor copia o parser de filtros ou pagina materialmente todos os resultados em PHP.

## Contrato HTTP

```http
GET /api/v1/transactions?page=1&per_page=10&order_by=date&sort_by=desc&date[gte]=2026-11-01&date[lte]=2026-11-30&search=mercado
```

```json
{
  "success": true,
  "message": "",
  "data": [],
  "pagination": {"current_page": 1, "per_page": 10, "total": 0, "last_page": 1},
  "errors": null,
  "meta": {"stats": {}, "categories": []}
}
```

- `page`: inteiro a partir de 1, omissão 1. Página além do fim devolve `data: []` mantendo o total.
- `per_page`: 1–100, omissão 10; configuração em `app.api_pagination`. Não existe tamanho ilimitado.
- `order_by`: chave da whitelist da entidade; omissão pela chave estável definida no paginator. Campo desconhecido → 422.
- `sort_by`: `asc` ou `desc`, omissão definida no paginator. Direção inválida → 422.
- A chave por defeito desempata valores iguais. A união de notificações desempata por origem e ID, pois as duas tabelas podem ter o mesmo ID.
- `search`: string até 200 caracteres, aparada. Vazio não restringe. Usa parâmetros SQL ligados e pesquisa literal: `%`, `_` e `!` não se tornam wildcards. Não aceita regex nem `search[value]`.
- `start`, `length`, `columns`, `order` e `draw` são proibidos e dão 422. As respostas não incluem `recordsTotal` nem `recordsFiltered`.
- Validação devolve 422 JSON com erros por campo, também sem Accept header.

## Filtros reutilizáveis

```http
GET /api/v1/accounts?active[eq]=0
GET /api/v1/transactions?date[gte]=2026-11-01&date[lte]=2026-11-30
GET /api/v1/transactions?account_id[in][]=1&account_id[in][]=2
```

`allowedOperatorsFields` limita campos e operadores; `valueRules` valida tipos/valores; `translateFields` qualifica colunas. Operadores implementados: `eq`, `ne`, `gt`, `gte`, `lt`, `lte`, `in`, apenas quando permitidos no campo. `in` aceita uma lista com até 100 valores ou a notação anterior `[1,2]`. Não se deduz `in` a partir de um `[` dentro de outro valor.

Valores `0`, `"0"` e `false` não são descartados. Um filtro vazio/null é omitido. Operador desconhecido ou valor malformado → 422. Campos desconhecidos não se tornam SQL e são ignorados, permitindo parâmetros independentes como `includeSummary`.

Um escalar equivale a `eq` quando esse operador é permitido. Aliases camelCase existentes são traduzidos por `aliases`, preservando condições canónicas já fornecidas. Ex.: `accountId` → `account_id`, `dateFrom` → `date[gte]`. O cliente deve preferir os nomes canónicos.

## Entidades e campos

| Listagem | Filter canónico | Search | Ordenação pública |
|---|---|---|---|
| Contas | type, active, currency_id | nome | id, name, type, balance |
| Transações | status, category_id, account_id, user_id, type, date | descrição, nome da conta/autor | id, account_id, date, amount, type, status |
| Dívidas | status | nome, descrição | id, name, total_amount, paid_amount, interest_rate, remaining_amount, status |
| Pagamentos | status, debt_id, user_id, date | nome da dívida, descrição | id, debt, amount, interest_paid, date, status |
| Metas | status, priority | nome, descrição | id, name, total_amount, contributed_amount, priority, status |
| Movimentos de metas | type, status, user_id, financial_goal_id, date | descrição | id, date, amount, type, status |
| Recorrentes | type, category_id, is_active, frequency, account_id, user_id, start_date, end_date | título, descrição, conta | id, title, amount, frequency, start_date, is_active, next_occurrence |
| Convites por domínio | status | remetente, destinatário, nome do recurso | id, created_at, status |
| Amigos/bloqueados, pedidos | âmbito/status fixado pelo endpoint | nome/id do outro participante | id, created_at |
| Descoberta de utilizadores | sem filtros próprios | nome, username, id | id, name, username |
| Atividade | user_id | nome do autor | id, created_at |
| Notificações | sem filtros próprios | título/mensagem do template | created_at |
| SharedRoles/SharedPermission | code | código | id, code |

Pesquisa de notificações cobre o texto guardado nos templates, não substituições de keywords ou apenas o idioma atual. `remaining_amount` das dívidas inclui juros conforme o helper financeiro. `next_occurrence` segue o calendário e os limites da recorrência, incluindo overflow de mês/ano; o SQL tem variantes MySQL/SQLite.

## Totais e categorias

- `pagination.total` conta o conjunto filtrado e pesquisado visível, antes de paginar.
- `meta.stats` das transações usa a mesma consulta, sem paginação, convertendo valores para a moeda preferida. Apenas movimentos completed entram em totais financeiros.
- Opções `meta.categories` reaplicam âmbito, filtros e pesquisa, omitindo apenas o filtro de categoria; clonar Request preserva o user resolver.
- Resumo opcional `meta.summary` de recorrentes é global às contas visíveis, independente de filtros/página, e continua a ser partilhado com o dashboard.
- Os agregados preexistentes de contas, dívidas e metas continuam globais. Nas contas, totais monetários usam `updateUserBalance`; estes agregados podem ter âmbito diferente das linhas e não devem ser apresentados como soma da página.

## Adicionar uma entidade

Criar `<Entity>Filter` e `<Entity>Search` com as whitelists; injetá-los em `<Entity>Paginate`; implementar `query` com visibilidade e eager loading; associar `RESOURCE`. No controller:

```php
public function index(Request $request, ExamplePaginate $paginate): JsonResponse
{
    return $this->safe(fn () => $this->respondModulePaginated($paginate, $request));
}
```

Testes do domínio ficam em `Modules/<Module>/Tests/Feature` ou `Unit` e são registados em `phpunit.xml`. Testar pertença, permissão, pesquisa agrupada, intervalo de datas, valores zero, ordenação estável, contagens e serialização relevante. Testes do contrato transversal ficam em `tests/`.

## Migração dos consumidores

O frontend Next usa `shared/api/module-listing.ts` e `module-listing.server.ts`. Traduz o índice 0 da UI de tabelas/cards para a página 1 da API; listas infinitas sociais/convites mantêm índice 1. O frontend lê `pagination.total`, preserva erros 401/403/422 e obtém extras a partir de meta. Os proxies deixam de enviar a descrição de colunas de DataTables.

Back e Front devem ser publicados de forma coordenada: os clientes antigos que enviam o protocolo DataTables recebem 422. A app Flutter/outros consumidores têm de adotar este contrato antes da entrega da versão. Não há nova migration nesta refatoração.

A divisão de issues, branches e PRs está no [registo da migração](../plans/module-listing-migration.md). Nenhum PR é integrado sem confirmação explícita do responsável.
