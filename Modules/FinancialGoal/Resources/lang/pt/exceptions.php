<?php

return [
    'financial-goal-transactions' => [
        'contributionExceedsTotalAmountException'              => 'A contribuição de :contribution excede o total de :totalAmount.',
        'contributionBeforeCurrentDateException'               => 'Não é possível adicionar uma contribuição antes da data atual.',
        'contributionNotScheduledException'                    => 'Não é possível confirmar a contribuição, pois ela não está agendada.',
        'unauthorizedConfirmFinancialGoalTransactionException' => 'Você não tem permissão para confirmar transações nesta meta financeira.',
        'unauthorizedCreateFinancialGoalTransaction'           => 'Você não tem permissão para criar transações nesta meta financeira.',
        'unauthorizedDeleteFinancialGoalTransactionException'  => 'Você não tem permissão para excluir transações nesta meta financeira.',
        'unauthorizedUpdateFinancialGoalTransactionException'  => 'Você não tem permissão para atualizar transações nesta meta financeira.',
        'unauthorizedViewFinancialGoalTransactionException'    => 'Você não tem permissão para visualizar transações nesta meta financeira.',
    ],

    // Metas financeiras
    'financial-goals'             => [
        'financialGoalNotFullyFundedException' => 'A meta financeira não pode ser concluída porque não está totalmente financiada.',
        'financialGoalNotInProgressException'  => 'A meta financeira não está em andamento.',
        'financialGoalInProgressException'     => 'A meta financeira está em andamento.',
        'unauthorizedDeleteFinancialGoal'      => 'Você não tem permissão para excluir esta meta financeira.',
        'unauthorizedUpdateFinancialGoal'      => 'Você não tem permissão para atualizar esta meta financeira.',
        'unauthorizedViewFinancialGoal'        => 'Você não tem permissão para visualizar esta meta financeira.',
    ],

    'financial-goal-users'        => [
        'singleFinancialGoalCreatorViolationException' => 'Você não tem permissão para criar mais de uma meta financeira.',
    ],
];
