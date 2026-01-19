<?php
return [
    'debts'             => [
        'type'   => [
            'loan'        => 'Loan',
            'bill'        => 'Bill',
            'credit_card' => 'Credit Card',
            'other'       => 'Other',
        ],

        'status' => [
            'pending' => 'Pending',
            'paid'    => 'Paid',
        ],
    ],

    'debt-user-invites' => [
        'status' => [
            'pending' => 'Pending',
            'revoked' => 'Revoked',
        ],
    ],

    'debt-payments'     => [
        'type'   => [
            'revenue' => 'Revenue',
            'expense' => 'Expense',
        ],

        'status' => [
            'completed' => 'Completed',
            'pending'   => 'Scheduled',
        ],
    ],
];
