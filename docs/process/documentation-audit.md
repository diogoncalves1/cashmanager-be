# Revisão documental — 20 de novembro de 2026

## Referência e método

A revisão usa o código local de `Back/CashManager`, incluindo trabalho não commitado já existente. Não fixa um commit como representação de toda a árvore de trabalho. As alterações funcionais locais foram preservadas.

Foram consultados manifestos Composer/npm, configuração Laravel/PHPUnit, bootstrap e tratamento de erros, notas do projeto, rotas registadas, providers de tarefas, requests/resources/paginators/filtros/repositories e policies de contas/transações, gerador de recorrências e documentação técnica dos módulos.

A documentação de domínios preexistente foi mantida e ligada a uma navegação por objetivo. O inventário cobre todas as rotas registadas; a revisão detalhada de contratos concentrou-se nas diferenças da refatoração e nas áreas transversais. Não constitui execução exaustiva dos 139 endpoints API.

## Resultado

| Área | Alteração |
|---|---|
| Navegação | Índice por tarefa e tabela domínio → regras → contrato HTTP |
| Arranque | Requisitos, MySQL, adaptação do `.env.example`, dados iniciais e serviços necessários |
| Arquitetura | Mapa dos 18 módulos, dependências e fluxos de saldo/reserva/pagamento |
| API | Paginação própria de contas/transações, filtros, Resources aninhadas e erros globais |
| Operação | Deploy, cron único, timezone, workers `default`/`emails`, recuperação e diagnóstico |
| Testes | Suites realmente configuradas, testes existentes e limites de SQLite/cobertura |
| Manutenção | Fontes de verdade, versionamento, scripts de inventário e links |
| Dívida técnica | Gate/policy de contas, picker por permissão e diferenças de filtros/stats documentados com fonte |

## Verificação realizada

- `php artisan route:list --json`: executado com sucesso; 296 registos de rota, 139 com prefixo `api/`.
- Inventário Markdown regenerado a partir desse JSON com `scripts/route_inventory.py`.
- 451 links relativos verificados com `scripts/check_links.py`, sem destinos em falta (ficheiros/diretórios; não verifica âncoras nem URLs remotos).
- 17 exemplos JSON completos de contas/transações validados sintaticamente; formatos comparados com as Resources e controllers. Os valores são ilustrativos, não capturas de respostas de produção.
- Sintaxe dos scripts Python verificada sem criar artefactos de execução nas docs.

## Limites e trabalho futuro

Não foram executados migrations, deploy, chamadas à API externa, alterações de BD ou testes funcionais nesta tarefa documental. A suite PHP não foi corrida por não haver alterações funcionais; a validação desta entrega incide na documentação e no registo de rotas.

A resolução dos problemas de autorização/filtros exige uma alteração funcional e testes próprios. Os restantes problemas conhecidos mantêm a distinção entre confirmações desta revisão e observações da análise anterior.

A configuração ativa de produção, os dados de referência de emails/notificações e o comportamento dos handlers não foram certificados no servidor. Após esta revisão, a documentação foi movida para `Back/CashManager/docs`, dentro do repositório Git do backend. Os caminhos, comandos e scripts foram adaptados à nova localização.
