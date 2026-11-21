-- Diagnostico administrativo, apenas leitura. Usa a ligacao original, sem receipts.
-- Os resultados nao certificam que o rollback pode ser publicado sobre estes dados.

-- 1. Estado das migrations retiradas do codigo ativo.
SELECT migration, batch
FROM migrations
WHERE migration IN (
    '2026_11_01_180000_add_goal_reservations',
    '2026_11_02_180000_backfill_financial_goal_accounts',
    '2026_11_02_185000_backfill_financial_goal_request_receipts',
    '2026_11_02_190000_convert_legacy_goal_reservations'
)
ORDER BY migration;

-- 2. Conta de origem historica de cada movimento e diagnostico.
SELECT fgt.financial_goal_id, fg.name AS goal_name, fgt.id AS movement_id,
       fgt.type, fgt.status, fgt.amount, fgt.transaction_id,
       t.account_id, a.name AS account_name,
       t.type AS transaction_type, t.status AS transaction_status,
       t.amount AS transaction_amount,
       CASE
           WHEN fg.id IS NULL THEN 'missing_goal'
           WHEN fgt.transaction_id IS NULL THEN 'missing_transaction_link'
           WHEN t.id IS NULL THEN 'missing_transaction'
           WHEN a.id IS NULL THEN 'missing_account'
           WHEN fg.currency_id IS NULL OR a.currency_id IS NULL THEN 'unknown_currency'
           WHEN fg.currency_id <> a.currency_id THEN 'currency_mismatch'
           WHEN fgt.type NOT IN ('contribution', 'withdrawal') THEN 'non_legacy_movement'
           WHEN fgt.amount IS NULL OR fgt.amount <= 0 THEN 'invalid_amount'
           WHEN t.amount IS NULL OR fgt.amount <> t.amount THEN 'amount_mismatch'
           WHEN t.status IS NULL OR fgt.status <> t.status THEN 'status_mismatch'
           WHEN (fgt.type = 'contribution' AND t.type = 'expense'
                 AND c.code = 'financialGoalContribution')
             OR (fgt.type = 'withdrawal' AND t.type = 'revenue'
                 AND c.code = 'financialGoalWithdrawal') THEN 'linked'
           ELSE 'type_or_category_mismatch'
       END AS diagnosis
FROM financial_goal_transactions fgt
LEFT JOIN financial_goals fg ON fg.id = fgt.financial_goal_id
LEFT JOIN transactions t ON t.id = fgt.transaction_id
LEFT JOIN accounts a ON a.id = t.account_id
LEFT JOIN categories c ON c.id = t.category_id
ORDER BY fgt.financial_goal_id, fgt.id;

-- 3. Historico liquido por meta/conta; nao e ainda o reservado do novo modelo.
-- Origens desconhecidas e valores negativos permanecem visiveis.
SELECT fgt.financial_goal_id, a.id AS account_id, a.name AS account_name,
       COUNT(*) AS movement_count,
       SUM(CASE WHEN fgt.status = 'completed' AND fgt.type = 'contribution'
                THEN fgt.amount ELSE 0 END) AS completed_contributions,
       SUM(CASE WHEN fgt.status = 'completed' AND fgt.type = 'withdrawal'
                THEN fgt.amount ELSE 0 END) AS completed_withdrawals,
       SUM(CASE WHEN fgt.status = 'completed' AND fgt.type = 'contribution' THEN fgt.amount
                WHEN fgt.status = 'completed' AND fgt.type = 'withdrawal' THEN -fgt.amount
                ELSE 0 END) AS historical_net,
       SUM(CASE WHEN fgt.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
       SUM(CASE WHEN a.id IS NULL THEN 1 ELSE 0 END) AS missing_origin_count
FROM financial_goal_transactions fgt
LEFT JOIN transactions t ON t.id = fgt.transaction_id
LEFT JOIN accounts a ON a.id = t.account_id
GROUP BY fgt.financial_goal_id, a.id, a.name
ORDER BY fgt.financial_goal_id, a.id;

-- 4. Diferenca entre total guardado e movimentos, incluindo metas sem movimentos.
-- Consultar tambem o diagnostico: moedas/tipos diferentes exigem reconciliacao.
SELECT fg.id AS financial_goal_id, fg.name AS goal_name,
       fg.contributed_amount AS stored_total,
       COALESCE(h.historical_net, 0) AS historical_net,
       fg.contributed_amount - COALESCE(h.historical_net, 0) AS difference,
       COALESCE(h.linked_internal_net, 0) AS linked_internal_net,
       fg.contributed_amount - COALESCE(h.linked_internal_net, 0) AS linked_difference,
       COALESCE(h.movement_count, 0) AS movement_count,
       COALESCE(h.missing_transaction_count, 0) AS missing_transaction_count,
       COALESCE(h.non_legacy_count, 0) AS non_legacy_count
FROM financial_goals fg
LEFT JOIN (
    SELECT fgt.financial_goal_id, COUNT(*) AS movement_count,
           SUM(CASE WHEN fgt.status = 'completed' AND fgt.type = 'contribution' THEN fgt.amount
                    WHEN fgt.status = 'completed' AND fgt.type = 'withdrawal' THEN -fgt.amount
                    ELSE 0 END) AS historical_net,
           SUM(CASE WHEN t.status = 'completed' AND fgt.type = 'contribution'
                         AND t.type = 'expense' AND c.code = 'financialGoalContribution' THEN t.amount
                    WHEN t.status = 'completed' AND fgt.type = 'withdrawal'
                         AND t.type = 'revenue' AND c.code = 'financialGoalWithdrawal' THEN -t.amount
                    ELSE 0 END) AS linked_internal_net,
           SUM(CASE WHEN t.id IS NULL THEN 1 ELSE 0 END) AS missing_transaction_count,
           SUM(CASE WHEN fgt.type NOT IN ('contribution', 'withdrawal') THEN 1 ELSE 0 END) AS non_legacy_count
    FROM financial_goal_transactions fgt
    LEFT JOIN transactions t ON t.id = fgt.transaction_id
    LEFT JOIN categories c ON c.id = t.category_id
    GROUP BY fgt.financial_goal_id
) h ON h.financial_goal_id = fg.id
ORDER BY fg.id;
