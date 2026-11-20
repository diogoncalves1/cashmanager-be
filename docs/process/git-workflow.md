# Fluxo de trabalho Git — CashManager (backend)

| | |
|---|---|
| **Repositório** | `cashmanager-be` (`github.com/diogoncalves1/cashmanager-be`) |
| **Ramo principal** | `main` |
| **Ramos de versão** | `develop/vX.Y.Z` (ex.: `develop/v1.6.2`) |
| **Data** | 2026-11-18 |
| **Decisor** | Responsável do projeto |

## 1. Branches

- Cada funcionalidade ou correção é desenvolvida numa **branch própria**.
- **Base:** cria-se a partir do `main`. Se houver um ramo de versão aberto para essa entrega, cria-se a partir dele (`develop/vX.Y.Z`).
- O nome tem a forma **`<tipo>/<descrição>`**:
  - **tipo** — indica a natureza do trabalho (tabela abaixo);
  - **descrição** — diz **apenas** o que se faz, em inglês, em minúsculas e com hífenes.
- O nome **não inclui** o identificador do ticket (`CM-xxx`). O ticket vai na descrição do Pull Request.
- **Exceção:** quando uma feature depende de outra que ainda não foi integrada, a branch nova é criada a partir da branch dessa feature. Na integração, junta-se primeiro a branch de que depende.

| Tipo | Quando usar |
|---|---|
| `feature/` | Nova funcionalidade |
| `bugfix/` | Correção de um erro encontrado em desenvolvimento ou revisão |
| `hotfix/` | Correção urgente de um erro em produção (a partir do `main`) |
| `refactor/` | Reorganização de código sem alterar o comportamento (só quando aprovada) |
| `chore/` | Manutenção: dependências, configuração, ferramentas, deploy |
| `docs/` | Apenas documentação |
| `develop/` | Ramo de versão que agrupa várias branches antes de ir para o `main` |

| Trabalho | Branch |
|---|---|
| Ledger de reservas das metas | `feature/goal-reservation-ledger` |
| Resumo global das contas | `feature/account-global-resume` |
| Limite de recusas de amizade que não dispara | `bugfix/friendship-decline-limit` |
| Permissão errada no reset de dívidas | `bugfix/debt-reset-permission` |
| Atualizar dependências do Composer | `chore/update-composer-dependencies` |
| Documentação da API | `docs/api-reference` |

> **Nomes antigos:** existem branches como `feature/CM-116`, `codex/...` e `CASHMANAGER-88`. São anteriores a esta regra e não devem ser usadas como modelo.

## 2. Commits

- Mensagens **breves e específicas** sobre o que mudou, em **inglês claro** e no imperativo.
- Formato: `<tipo>(<âmbito>): <descrição>`. É o formato que o histórico do repositório já usa.
  - **tipo:** `feat`, `fix`, `refactor`, `chore`, `docs`, `test` ou `revert`;
  - **âmbito:** o domínio ou módulo afetado (`accounts`, `goals`, `debts`, `recurring`, `categories`, `auth`, `notifications`…).
- Uma linha de assunto curta (até cerca de 72 caracteres); corpo só quando for mesmo necessário, para explicar o porquê.
- Um commit corresponde a uma alteração coerente. Não se misturam refactors com correções.

| Bom | A evitar |
|---|---|
| `feat(accounts): add scoped global resume endpoint` | `Changes` |
| `fix(recurring): return localized JSON validation errors` | `fix some bugs` |
| `fix(debts): check editDebt permission on reset` | `CM-120 work in progress` |
| `docs(api): document debt payment endpoints` | `update agents md` |

## 3. Commit e push

- Só se faz commit quando o responsável do projeto o pedir.
- **Nunca** se faz push sem indicação explícita. Um pedido para criar PRs autoriza publicar as branches necessárias para esses PRs; não autoriza integrar.
- Nunca se reescreve histórico já publicado (`push --force`) sem autorização.
- Antes do commit:
  - corre-se `composer test`, ou o teste focado do módulo;
  - corre-se `vendor/bin/pint` nos ficheiros alterados.

## 4. Pull Requests e integração

- **Regra do responsável (20/11/2026):** preparar alterações, testes, commits e pull requests, e aguardar confirmação explícita do responsável antes de qualquer merge/integração. Não integrar automaticamente nem ativar auto-merge. A existência de PR ou checks verdes não substitui essa confirmação.
- Criar PRs separados por módulo/subcategoria. Quando há dependências, encadear as branches/PRs e indicar a ordem de integração na descrição.


- **Destino:**
  - PR da branch para `develop/vX.Y.Z`, se houver uma versão em curso;
  - caso contrário, PR diretamente para o `main`.
  - O ramo de versão entra no `main` através de um PR próprio.
- **Descrição do PR:**
  - ticket (`CM-xxx`);
  - resumo da alteração;
  - migrações que é preciso correr;
  - passos de deploy especiais;
  - testes executados.
- **Migrações de dados irreversíveis** (como as das metas, `2026_11_02_*`) exigem um documento de rollout, ao estilo de `Modules/FinancialGoal/PRODUCTION-ROLLOUT.md`, e um ensaio em MySQL 8 antes da integração.
- Se o comportamento, as rotas ou as tabelas mudarem, o PR atualiza também `Back/CashManager/docs/` (`api/` e `architecture/`) e, quando for relevante, o `AGENTS.md`.

## 5. Relação com o planeamento

Aplica-se a regra **"uma feature, uma implementação, uma revisão, uma aprovação"**:

1. A ideia ou pedido é registada em [`../intents/`](../intents/).
2. Se for necessário especificar regras ou contratos, isso é feito em [`../specs/`](../specs/).
3. O plano de implementação fica em [`../plans/`](../plans/).
4. Cada feature do plano tem a sua branch, e é revista e aprovada antes de se avançar para a seguinte.

## 6. Issues: projeto e versão obrigatórios

**Todas as issues**, independentemente do módulo ou repositório, devem ser adicionadas ao projeto GitHub [Cash Manager (CashManager)](https://github.com/users/diogoncalves1/projects/2) e ter o campo **Version** preenchido com a versão de entrega correspondente. Não basta escrever a versão no título/body, usar uma label ou definir apenas um milestone.

Confirmar a versão no planeamento/branch de entrega e nas iterações do projeto; não inventar uma versão nem atribuir a atual a trabalho destinado a outra entrega. Em 20/11/2026, a iteração corrente observada é **Version 1.6.2**; isso é contexto desta entrega, não um valor fixo para issues futuras.

Antes de dar a organização por concluída, verificar no GitHub que a issue aparece no projeto e que Version está selecionada. Se o conector não permitir editar Projects, usar a UI do GitHub. Se houver falta de acesso, registar a limitação concretamente e não afirmar que a associação foi feita.
