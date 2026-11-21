#!/usr/bin/env python3
"""Rebuild the Markdown inventory from `php artisan route:list --json`."""
import argparse
from collections import defaultdict
from datetime import date
import json
from pathlib import Path


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('routes_json', type=Path)
    parser.add_argument('--date', default=date.today().isoformat(), help='Review date (YYYY-MM-DD)')
    args = parser.parse_args()
    routes = json.loads(args.routes_json.read_text())
    groups = defaultdict(list)
    for route in routes:
        action = route['action']
        if route['uri'].startswith('api/'):
            group = 'API / ' + (action.split('\\')[1] if action.startswith('Modules\\') else 'Autenticação e aplicação')
        elif action.startswith(('Modules\\', 'App\\')):
            group = 'Web / ' + (action.split('\\')[1] if action.startswith('Modules\\') else 'Aplicação')
        else:
            group = 'Framework e pacotes'
        groups[group].append(route)
    api_count = sum(r['uri'].startswith('api/') for r in routes)
    lines = ['# Inventário de rotas registadas', '',
             f'Gerado em {args.date} a partir de `php artisan route:list --json`: **{len(routes)} registos**, dos quais **{api_count} de API**. Um registo pode aceitar vários métodos HTTP.', '',
             'Este é o mapa de registo da aplicação local, incluindo rotas de backoffice e pacotes. Não valida a execução dos handlers, permissões internas, dados ou configuração de produção. Contratos e exemplos: [convenções da API](README.md). Regeneração: [manutenção](../process/documentation.md).', '',
             'Os nomes de middleware abaixo são os devolvidos pelo Laravel. `Authenticate:sanctum` corresponde a autenticação Sanctum; `Authenticate` sem argumento usa o guard por omissão. Uma rota sem esse middleware pode continuar a ter verificações internas.', '']
    for group, entries in sorted(groups.items()):
        lines += [f'## {group}', '', '| Método | Caminho | Handler | Middleware |', '|---|---|---|---|']
        for route in sorted(entries, key=lambda r: (r['uri'], r['method'])):
            action = route['action']
            controller, _, method = action.partition('@')
            handler = controller.split('\\')[-1] + (f'@{method}' if method else '')
            if controller.startswith(('App\\', 'Modules\\')):
                path = controller.replace('\\', '/') + '.php'
                if path.startswith('App/'):
                    path = 'app/' + path[4:]
                if (Path(__file__).resolve().parents[2] / path).exists():
                    handler = f'[{handler}](../../{path})'
            middleware = ', '.join('`' + m.split('\\')[-1] + '`' for m in route['middleware']) or '—'
            methods = route['method'].replace('|', ', ')
            lines.append(f"| {methods} | `/{route['uri']}` | {handler} | {middleware} |")
        lines.append('')
    target = Path(__file__).resolve().parents[1] / 'api/route-inventory.md'
    target.write_text('\n'.join(lines) + '\n')
    print(f'{target}: {len(routes)} routes ({api_count} API)')


if __name__ == '__main__':
    main()
