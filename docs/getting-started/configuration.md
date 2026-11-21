# Configuração do backend

Fontes: `.env.example`, `config/*.php`, `Modules/Currency/Config/services.php` e providers dos módulos. Os valores reais de `.env` e de cópias de servidor não fazem parte desta referência.

| Configuração | Utilização / decisão |
|---|---|
| `APP_ENV`, `APP_DEBUG` | Ambiente e detalhe de erros; produção usa debug desligado |
| `APP_KEY` | Chave Laravel; gerar numa instalação nova e conservar durante atualizações |
| `APP_URL` | URL pública do backend |
| `FRONTEND_URL` | Origem usada nos links de verificação e recuperação; lida por `config('app.frontend_url')` |
| `APP_LOCALE`, `APP_FALLBACK_LOCALE` | Idioma inicial/fallback; pedidos autenticados com `setlocale` usam a preferência do utilizador |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Ligação MySQL; separar bases local/teste/produção |
| `CACHE_STORE` | Cache de notificações/permissões e locks do scheduler; exige armazenamento partilhado entre instâncias para `onOneServer` |
| `QUEUE_CONNECTION` | `sync`: execução imediata; `database` ou `redis`: requer worker |
| `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD` | Só necessários se algum driver usar Redis |
| `SESSION_DRIVER` | Backoffice por sessão; `database` requer a tabela `sessions` |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Transporte de email; `log` permite inspecionar envios locais, mas não dispensa os templates na BD |
| `CURRENCY_API_KEY` | Chave de `exchangerate-api.com` usada na atualização de câmbios |
| `LOG_CHANNEL`, `LOG_LEVEL` | Destino e nível dos logs Laravel |

## Configuração que está no código

- `config/app.php`: timezone da aplicação `UTC`, prefixo base `api`, paginação por omissão 10 e máximo 100.
- `CurrencyServiceProvider`: atualização diária às 01:00 em `Europe/Lisbon`, com mudança de hora portuguesa.
- `RecurringTransactionServiceProvider`: tarefa `daily()`; sem timezone próprio, segue o da aplicação.
- Os providers de rotas dos módulos também contêm prefixos `api`. Alterar apenas `app.api_prefix` não garante que todas as rotas mudem em conjunto.

## Perfis de execução

| Contexto | Base de dados | Cache | Fila | Email |
|---|---|---|---|---|
| `.env.example` | SQLite | database | database | log |
| Testes (`phpunit.xml`) | SQLite em memória | array | sync | array |
| Ambiente completo de desenvolvimento | MySQL 8 | driver configurado | driver configurado + worker se assíncrono | log ou SMTP de testes |

O exemplo do Laravel precisa de adaptação para este projeto. Não inferir a configuração ativa de produção a partir de `Back/env` ou de notas históricas.

Depois de alterar variáveis, confirmar se existe configuração em cache. Usar `php artisan config:clear` no desenvolvimento e reconstruir com `php artisan config:cache` no deploy. Não publicar `.env`, tokens, credenciais ou relatórios financeiros.
