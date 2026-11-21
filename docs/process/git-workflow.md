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
- **Ao abrir um PR, passar imediatamente as issues associadas para `Code Complete` no projeto Cash Manager.** Confirmar o estado no campo Status do projeto. Code Complete significa código pronto e PR aberto para revisão; não autoriza merge nem fecho da issue.
- Criar PRs separados por módulo/subcategoria. Quando há dependências, indicar a ordem de integração na descrição. A origem pode conter dependências, mas todos os PRs da entrega têm como destino `develop/vX.Y.Z` da versão correspondente; não usar outra branch de funcionalidade como destino do PR.


- **Destino:**
  - PR da branch de código para `develop/vX.Y.Z` da versão indicada na issue (por exemplo, Version 1.6.2 → `develop/v1.6.2`); confirmar que a branch existe no repositório;
  - se o ramo de entrega ainda não existir, confirmar/criar o ramo da versão planeada antes de abrir o PR; não substituir o destino por `main` ou por outra funcionalidade.
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

A issue deve estar associada à branch real do código na secção **Development** do GitHub. Criar a branch a partir da issue ou associar a branch existente nesse seletor. Quando já existe PR aberto para a branch, o GitHub apresenta a associação através desse PR: selecionar o PR correspondente e confirmar que a sua head é a branch de código da issue. Uma referência textual no body, o nome do ticket ou `Refs #n` não substituem esta associação. Ver [documentação do GitHub](https://docs.github.com/en/issues/tracking-your-work-with-issues/using-issues/linking-a-pull-request-to-an-issue).

Antes de dar a organização por concluída, verificar no GitHub: projeto Cash Manager, Version correta, associação em Development à branch/PR correto, head do PR igual à branch de código e base igual a `develop/vX.Y.Z` dessa versão. Ao abrir o PR, confirmar também Status = Code Complete. Se o conector não permitir editar Projects, usar a UI do GitHub. Se houver falta de acesso, registar a limitação concretamente e não afirmar que a associação foi feita.
