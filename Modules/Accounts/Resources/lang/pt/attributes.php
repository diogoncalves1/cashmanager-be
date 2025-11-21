<?php
return [
    // Accounts
    'accounts'             => [
        'type'   => [
            'bank_account'   => 'Conta Bancária',
            'credit_card'    => 'Cartão de Crédito',
            'cash'           => 'Dinheiro',
            'digital_wallet' => 'Carteira Digital',
        ],

        'status' => [
            'active'   => 'Activo',
            'disabled' => 'Desactivado',
        ],
    ],

    // Account User Invites
    'account-user-invites' => [
        'status' => [
            'pending' => 'Pendente',
            'revoked' => 'Revogado',
        ],
    ],

    // Transactions
    'transactions'         => [
        'type'   => [
            'revenue' => 'Receita',
            'expense' => 'Despesa',
        ],

        'status' => [
            'completed' => 'Concluída',
            'pending'   => 'Agendada',
        ],
    ],
];
