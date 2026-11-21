# Diagnóstico de problemas

Começar pelo método/rota, HTTP status e corpo JSON. Recolher logs sem partilhar tokens, passwords ou dados financeiros. Os detalhes de erros conhecidos estão em [known-issues](../architecture/known-issues.md).

| Sintoma | Verificar primeiro | Próximo passo |
|---|---|---|
| API devolve HTML ou redireciona | `Accept: application/json`, prefixo e middleware da rota | Consultar `bootstrap/app.php` e inventário; rotas Currency de escrita podem usar sessão web |
| 401 | Bearer token e token atual na BD | Confirmar login; distinguir autenticação de exceções de partilha que também usam 401 |
| 403 ou 500 numa ação de conta | Membership, shared role, policy e Gate chamado | `AccountRepository` chama `destroy`, mas `AccountPolicy` define `delete`; consultar a auditoria local |
| 422 | `errors` por campo, payload e FormRequest | Validar datas, categorias, moeda e bloqueios das metas; o handler global acrescenta envelope em erros renderizados |
| `page`/`per_page` inválido | Inteiros positivos; máximo 100 em `per_page` | Usar o contrato de paginação própria de contas/transações |
| Lista vazia ou frontend sem paginação | Contrato de cada endpoint | Contas/transações usam `pagination`, não `recordsTotal`; não enviar filtros DataTables para estes endpoints |
| Linhas e stats de transações não coincidem | Formato dos filtros | As linhas usam filtros com operador; stats/categorias ainda leem parâmetros legados |
| Erro `FIND_IN_SET`/`DATE_ADD`/view | Driver de BD e migrations | Usar MySQL 8 para integração completa; SQLite só cobre o setup isolado dos testes |
| Erro `ONLY_FULL_GROUP_BY` | Joins com views agregadas, query de count | Conferir migrations das views e o padrão `whereExists` em movimentos de metas |
| Registo/reset falha ao enviar email | Layout/tipo na BD, `FRONTEND_URL` e mailer | Conferir cadeia observer → listener → notification → mailable |
| Amizade falha ao notificar | Tipos `FRIEND_REQUEST_*` | Confirmar configuração do backoffice; não assumir seeder |
| Recorrência não aparece | Ativa, datas, cron, fila e `last_generated_at` | Consultar scheduler e worker; não alterar o cursor de geração para forçar repetição |
| Broadcast não envia email | `send_email`, fila `emails`, worker | O worker padrão do script dev não cobre essa fila por omissão |
| Câmbios não atualizam | Chave, cron, timezone, locks e log específico | A tarefa corre às 01:00 em Lisboa; não criar cron paralelo |
| Feed desatualizado | Cache e preferências | O feed tem TTL de 5 minutos; verificar também erros de broadcasts conhecidos |
| Reserva ou saldo inesperado | Contribution/withdrawal/usage e histórico migrado | Comparar ledger e contexto da conta; seguir preview do rollout, sem corrigir valores por suposição |

## Comandos de leitura

Executar em `Back/CashManager`:

```bash
php artisan route:list --path=api
php artisan schedule:list
php artisan migrate:status
php artisan queue:failed
php artisan goals:reservation-rollout
```

O preview do rollout não escreve sem `--apply`. Os comandos de estado ligados a BD/cache precisam dos serviços configurados. A listagem de rotas comprova registo, não exercita controllers ou permissões.

## Quando atualizar as docs

Um problema reproduzido deve indicar rota/entrada, resultado observado, resultado pretendido e fonte no código. Corrigir o contrato do domínio se estiver desatualizado. Não promover um bug provável a comportamento garantido sem reprodução ou leitura conclusiva do fluxo.
