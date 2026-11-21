# Instalação e execução local

Executar os comandos a partir de `Back/CashManager`, onde estão `artisan` e `composer.json`.

## Requisitos

- PHP compatível com `composer.lock` (o projeto declara `^8.2`) e Composer.
- Extensões exigidas pelo lock; confirmar com `composer check-platform-reqs`. Para MySQL é necessário o driver PDO correspondente.
- MySQL 8: as migrations usam views, `GROUP_CONCAT`, `FIND_IN_SET` e SQL específico de MySQL. O `.env.example` aponta para SQLite, mas esse valor não representa um arranque completo suportado pelas migrations do domínio.
- Node.js compatível com Vite 7 e npm, para os assets Blade do backend. O frontend Next.js é outro projeto.
- Redis se for escolhido para cache ou filas. `REDIS_CLIENT=predis` usa a dependência Composer instalada; `phpredis` exige a extensão PHP.
- Python e dependências do script financeiro apenas para funcionalidades do módulo Asset.

## Preparar uma instalação nova

```bash
cd Back/CashManager
composer install
cp .env.example .env
php artisan key:generate
```

Copiar o exemplo apenas quando ainda não existir `.env`. Configurar uma base de dados MySQL própria, `APP_URL`, `FRONTEND_URL` e os drivers de [configuração](configuration.md) antes de migrar.

```bash
composer check-platform-reqs
php artisan migrate
npm install
npm run build
php artisan route:list --path=api
```

Se existir um lock npm versionado, pode usar-se `npm ci` em vez de `npm install`.

`composer run setup` automatiza instalação, criação do `.env`, chave, migrations e assets, mas executa `migrate --force` antes de se poder corrigir interativamente a configuração. Usá-lo só depois de preparar o ambiente. Não substitui o roteiro de rollout das metas numa instalação existente.

## Dados iniciais e primeiro utilizador

`database/seeders/DatabaseSeeder.php` está vazio. Parte dos dados de referência é criada nas próprias migrations (roles, permissões e outros catálogos). A existência desses dados não garante configuração funcional de email e notificações.

Antes de testar registo, recuperação de password e amizades, confirmar os layouts/tipos de email e os tipos `FRIEND_REQUEST_*` descritos em [serviços transversais](../architecture/cross-cutting-services.md). Os observers de utilizador podem disparar emails durante a criação. Não assumir credenciais administrativas universais nem copiar segredos de ambientes existentes para as docs.

## Executar

```bash
composer run dev
```

O script inicia HTTP (`artisan serve`), fila (`queue:listen --tries=1`), logs (`pail`) e Vite. Não inicia o scheduler nem um worker dedicado à fila `emails`.

Em terminais separados, quando essas funcionalidades forem necessárias:

```bash
php artisan schedule:work
php artisan queue:work --queue=emails,default --tries=1
```

Com `QUEUE_CONNECTION=sync`, os jobs correm durante o pedido/agendamento e não precisam de worker. Com fila assíncrona, o scheduler enfileira recorrências e o worker executa-as.

## Confirmar o arranque

1. `GET /up`: verifica se a aplicação arranca; não certifica todo o domínio ou integrações.
2. Consultar `route:list` e [inventário de rotas](../api/route-inventory.md).
3. Fazer login com um utilizador verificado e consultar `/api/v1/me` com Bearer token.
4. Confirmar preferências de idioma/moeda antes dos endpoints que as consultam.
5. Executar os [testes focados](../process/testing.md).

Problemas de arranque, emails, filas ou SQL: [diagnóstico](../operations/troubleshooting.md).
