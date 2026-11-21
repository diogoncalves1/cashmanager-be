# Operação e deploy

Este guia descreve os componentes exigidos pelo código. Não certifica o estado de um servidor. Configuração: [variáveis e drivers](../getting-started/configuration.md).

## Componentes em execução

| Componente | Função | Confirmar |
|---|---|---|
| HTTP/PHP | API e backoffice | Document root em `public/`, `/up`, login e rota autenticada |
| MySQL 8 | Dados, views e migrations | Compatibilidade das views e `ONLY_FULL_GROUP_BY` |
| Cron do scheduler | Despacha tarefas vencidas | Uma execução de `artisan schedule:run` por minuto |
| Worker de filas | Executa jobs quando a fila é assíncrona | Processar `default` e `emails` |
| Cache | Feed, permissões e locks | Backend e namespace consistentes; partilhado entre instâncias |
| SMTP | Emails de conta e notificações | Transporte + layouts/tipos existentes na BD |

O ficheiro `Back/htaccess` é uma referência de alojamento; confirmar a configuração real. O diretório servido deve proteger `.env`, código, cópias e relatórios.

## Antes da publicação

1. Identificar versão, migrations pendentes e alterações de contrato com frontend/mobile.
2. Guardar backup recuperável da BD e configuração; ensaiar migrations de dados numa cópia MySQL.
3. Consultar o [rollout das reservas](../../Modules/FinancialGoal/PRODUCTION-ROLLOUT.md) se as migrations das metas estiverem pendentes. Esse roteiro tem precedência sobre uma migration genérica.
4. Preparar dependências e assets da versão. Confirmar requisitos PHP com `composer check-platform-reqs`.
5. Confirmar credenciais de integrações, permissões de escrita em `storage/` e `bootstrap/cache/`, cache e filas.

## Publicação habitual

Comandos de referência a executar na raiz do backend, adaptados ao mecanismo de deploy e ao plano de migrations da versão:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan view:cache
php artisan queue:restart
```

`npm ci` pressupõe lock npm disponível; em deploys por artefacto os assets podem ser construídos antes de chegar ao servidor. `migrate --force` não substitui o rollout específico das metas. Preservar `APP_KEY` existente.

Se for necessária manutenção, suspender também produtores de movimentos: o modo de manutenção HTTP não substitui o controlo do scheduler/workers. Só reabrir após verificar migrations, saldos e reservas envolvidos na entrega.

## Scheduler e filas

Um único cron, com caminhos absolutos próprios do servidor:

```cron
* * * * * cd /caminho/para/Back/CashManager && /caminho/para/php artisan schedule:run >> /caminho/privado/scheduler.log 2>&1
```

| Tarefa | Quando | Execução |
|---|---|---|
| `currency:fetch-daily` | 01:00 Europe/Lisbon | Comando; saída em `storage/logs/currency-rates.log` |
| `GenerateRecurringTransactionsJob` | `daily()`, timezone da aplicação (UTC no código atual) | Enfileirado; execução inline apenas com fila sync |
| `SendNotificationEmailJob` | Quando há broadcast com email | Fila `emails` |

Ambas as tarefas agendadas usam `withoutOverlapping` e `onOneServer`. Este último requer cache apropriada e partilhada se houver várias instâncias. Um cron por tarefa duplicaria o agendamento existente.

Com fila assíncrona, manter um processo supervisionado equivalente a:

```bash
php artisan queue:work --queue=emails,default --tries=1
```

O script `composer run dev` escuta a fila padrão e não garante consumo de `emails`. Após publicar código, reiniciar workers persistentes. Não repetir jobs financeiros falhados sem primeiro verificar se houve gravação parcial ou geração da mesma ocorrência.

## Verificação após deploy

- `php artisan migrate:status`, `php artisan schedule:list` e `php artisan route:list --path=api`.
- `/up`, login e leitura de um recurso autorizado.
- Confirmar contratos de listas: contas/transações paginadas, restantes listas conforme o domínio.
- Se a entrega afetar finanças: comparar saldo, reservado, disponível, totais e histórico com o ensaio.
- Confirmar consumo das duas filas e ausência de novos erros em `storage/logs/laravel.log` e no log de câmbios.

## Recuperação

Uma falha de publicação de código não implica que se possa reverter migrations. As migrations financeiras das metas são forward-only. Usar o roteiro específico; recuperar backup e versão de código de forma coordenada, ou corrigir para a frente preservando novas escritas. Não usar `migrate:fresh` num ambiente com dados a conservar.
