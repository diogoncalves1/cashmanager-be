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
            'activated'   => 'ativada',
            'inactivated' => 'desativada',
            'active'      => 'Ativo',
            'disabled'    => 'Desativado',
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
