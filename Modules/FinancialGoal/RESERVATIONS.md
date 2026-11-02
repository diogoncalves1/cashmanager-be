# Metas: um único modelo de reservas

Atualizado em 2026-11-02, CM-120 / CM-250. A distinção `legacy`/`reservation` foi retirada do runtime, da interface e dos contratos. Todas as metas usam movimentos de reserva/libertação/utilização. A migration `230000` elimina `funding_mode` depois de converter o histórico completo.

## Regras

- Reservar/libertar mantém o saldo. Saldo negativo é informativo, sem avisos de insuficiência ou bloqueio de movimentos.
- Várias contas da mesma moeda; libertar/utilizar limita-se à reserva da meta na conta. Não inferir contas nem misturar moedas.
- Utilizar cria uma despesa ou associa uma já existente, sem a debitar duas vezes. Desassociar mantém a despesa e repõe a reserva.
- Apenas movimentos concluídos entram nos cálculos. Reservado = contribuições − levantamentos − utilizações; financiamento exclui a subtração das utilizações. O ledger é a fonte, não o total em cache.
- Atingir 100% ou concluir não cria uma despesa. Conclusão preserva o financiamento; reservas restantes podem ser utilizadas/libertadas.
- Movimentos concluídos são corrigidos por movimentos opostos; utilizações podem ser desassociadas. Pendentes podem ser alterados/removidos e confirmados na data devida ou posterior.
- Permissões da conta/meta, locks, recibos de novos pedidos e identidade de recorrentes mantêm consistência perante repetição e concorrência.

## Migração e regressão da meta 7

Ver [PRODUCTION-ROLLOUT.md](PRODUCTION-ROLLOUT.md) para comandos e recuperação. Converte cada movimento sem saltar metas anteriormente marcadas como convertidas. Recupera contas via `transaction_id`, neutraliza efeitos reais uma vez, preserva o ledger e audita diferenças. Reservas recentes e compras reais não recebem reposição indevida.

O export analisado tem 131,40 € de contribuições − 10 € de levantamentos = 121,40 € reservados na meta 7. Os 10 € recentes eram apenas um dos 22 movimentos; 21 antigos continuavam sem conta direta. O teste usa apenas os valores necessários, sem copiar descrições ou dados pessoais para o repositório.

Não existem metas deixadas num modelo anterior: referências impossíveis de recuperar abortam atomicamente a conversão completa. Auditorias de conversões anteriores podem fornecer a origem exata, sem inventar contas ou fabricar recibos.

## Contrato

- `GET /api/v1/financial-goals/{id}/reservations`: valores de reserva/utilização/financiamento e contas, sem `fundingMode`.
- `GET /api/v1/financial-goals/{id}/eligible-expenses`: despesas paginadas por conta.
- `POST /api/v1/financial-goal-transactions`: contribuição, levantamento ou utilização; `request_key` obrigatório. `transaction_id` associa compra existente; `category_id` cria despesa.
- Atualização/eliminação/confirmação mantêm endpoints e erros HTTP 422 por campo.
- `php artisan goals:reservation-rollout` lê o histórico; `--goal=7` filtra; `--apply` converte o conjunto atomicamente. `--fail-on-review` permite verificação automatizada.

Verificação: suites `GoalReservationsTest`, `PersonalCategoriesTest`, `RecurringSummaryTest`, `TranslationCatalogueTest`, `AccountResumeTest`; FE `test:goals`, `test:categories`, `test:recurring`, `typecheck`, `build`. MySQL 8.0.40 isolado validado. A BD real não foi alterada.
