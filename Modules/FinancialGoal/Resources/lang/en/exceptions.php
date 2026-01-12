<?php

return [
    'financial-goal-transactions' => [
        'contributionExceedsTotalAmountException'              => 'Contribution of :contribution exceeds total of :totalAmount.',
        'contributionBeforeCurrentDateException'               => 'Unable to add a contribution before the current date.',
        'contributionNotScheduledException'                    => 'Unable to confirm contribution as it is not scheduled.',
        'unauthorizedConfirmFinancialGoalTransactionException' => 'You do not have permission to confirm transaction on this financial goal.',
        'unauthorizedCreateFinancialGoalTransaction'           => 'You do not have permission to create transaction on this financial goal.',
        'unauthorizedDeleteFinancialGoalTransactionException'  => 'You do not have permission to delete transaction on this financial goal.',
        'unauthorizedUpdateFinancialGoalTransactionException'  => 'You do not have permission to update transaction on this financial goal.',
        'unauthorizedViewFinancialGoalTransactionException'    => 'You do not have permission to view transaction on this financial goal.',
    ],

    // Financial Goals
    'financial-goals'             => [
        'financialGoalNotFullyFundedException' => 'The financial goal cannot be completed because it is not fully funded.',
        'financialGoalNotInProgressException'  => 'The financial goal is not in progress.',
        'financialGoalInProgressException'     => 'The financial goal is in progress.',
        'unauthorizedDeleteFinancialGoal'      => 'You do not have permission to delete this financial goal.',
        'unauthorizedUpdateFinancialGoal'      => 'You do not have permission to update this financial goal.',
        'unauthorizedViewFinancialGoal'        => 'You do not have permission to view this financial goal.',
    ],

    'financial-goal-users'        => [
        'singleFinancialGoalCreatorViolationException' => 'You are not allowed to create more than one financial goal.',
    ],
];
