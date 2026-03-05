<?php

return [
    // Financial Goals
    'financial_goal' => [
        'titles'   => [
            'goal_created'   => 'Meta criada',
            'user_invited'   => 'Utilizador convidado',
            'goal_paused'    => 'Meta cancelada',
            'goal_reseted'   => 'Meta resetada',
            'goal_completed' => 'Meta completada',

        ],

        'messages' => [
            'goal_created'   => 'Meta inicial: :initialTarget',
            'user_invited'   => ':userName convidado como :roleName',
            'goal_paused'    => 'A meta financeira foi cancelada',
            'goal_reseted'   => 'A meta financeira voltou a estar pendente',
            'goal_completed' => 'A meta financeira foi completa',

        ],
    ],

    // Debts
    'debt'           => [
        'titles'   => [
            'debt_created' => 'Dívida criada',
        ],

        'messages' => [
            'debt_created' => 'Dívida inicial: :initialAmount',
        ],
    ],

    'account'        => [
        'titles'   => [
            'account_created' => 'Conta criada',
        ],

        'messages' => [
            'account_created' => 'Conta criada: :accountName',
        ],
    ],
];
