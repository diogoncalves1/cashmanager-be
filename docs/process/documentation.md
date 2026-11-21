# Manutenção da documentação

## Fonte de verdade

A documentação descreve o que o código faz. Uma regra proposta ou um refactor incompleto não se transforma em contrato implementado por aparecer numa classe nova.

| Informação | Fonte a consultar |
|---|---|
| Rota, método e middleware | `artisan route:list --json`, `routes/`, `Modules/*/Routes/` e providers |
| Validação | FormRequest, regras e validação adicional no repository/service |
| Resposta JSON | Controller → Resource/Collection → ApiResponder → handler em `bootstrap/app.php` |
| Listagem | Controller → ModulePaginate → ModuleFilter/ModuleSearch → Resource |
| Autorização | Gate/policy realmente chamada, shared role e query de visibilidade |
| Saldo e estados | Repository/service, transação de BD, locks e testes |
| Tabela/view | Migrations executadas e modelo; a migration mais recente prevalece |
| Tarefa/fila | Provider de schedule, job e configuração do worker |
| Estado de produção | Evidência do servidor; nunca inferir apenas de uma cópia de `.env` |

## Onde colocar cada documento

- `getting-started/`: instalação e variáveis.
- `architecture/`: desenho atual, módulos, invariantes, base de dados e limitações conhecidas.
- `api/`: contratos HTTP e inventário de rotas.
- `operations/`: deploy, processos e diagnóstico.
- `process/`: desenvolvimento, testes, Git e manutenção das docs.
- `intents/`, `specs/`, `plans/`: proposta e decisão de trabalho futuro. Não duplicar aqui os contratos já documentados na API.

Manter os nomes dos ficheiros existentes quando possível para conservar ligações. Documentos técnicos do módulo (categorias e rollout das metas) continuam junto do código; ligar para eles em vez de manter cópias divergentes.

## Atualizar após alterações

1. Seguir o pedido da rota até à BD e à serialização; confirmar middleware e permissões.
2. Atualizar o contrato `api/<domínio>.md` e regras `architecture/modules/<domínio>.md`.
3. Se mudar tabela, serviço, configuração ou tarefa, atualizar a respetiva referência.
4. Remover problemas resolvidos e registar inconsistências novas com fonte e estado.
5. Atualizar o inventário se mudarem rotas/middleware. Registar data e âmbito da revisão.
6. Validar links e exemplos; manter `AGENTS.md` compacto, com remissão para as docs.

## Inventário reproduzível

A partir de `Back/CashManager`:

```bash
php artisan route:list --json > /tmp/cashmanager-doc-routes.json
python3 docs/scripts/route_inventory.py /tmp/cashmanager-doc-routes.json
python3 docs/scripts/check_links.py
```

O inventário contém rotas da aplicação e dos pacotes em secções distintas. A coluna de middleware vem do registo Laravel; a autorização interna e a execução do handler precisam de análise adicional.

## Versionamento

A documentação está em `docs/`, dentro do repositório `Back/CashManager`. Incluir os ficheiros de documentação alterados no mesmo commit/PR das mudanças de código correspondentes. Os comandos desta página são executados na raiz desse repositório.

Não incluir segredos, credenciais administrativas, payloads reais de utilizadores ou relatórios de rollout nas docs. Usar exemplos fictícios e placeholders.
