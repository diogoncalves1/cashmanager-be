<?php
return [
    'accounts'             => [
        'type'   => [
            'bank_account'   => 'Bank Account',
            'credit_card'    => 'Credit Card',
            'cash'           => 'Cash',
            'digital_wallet' => 'Digital Wallet',
        ],

        'status' => [
            'active'   => 'Active',
            'disabled' => 'Disabled',
        ],
    ],

    'account-user-invites' => [
        'status' => [
            'pending' => 'Pending',
            'revoked' => 'Revoked',
        ],
    ],

    'transactions'         => [
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
