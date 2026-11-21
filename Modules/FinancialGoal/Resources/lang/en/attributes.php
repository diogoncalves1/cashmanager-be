<?php

return [
    'financial-goals'             => [
        'status'   => [
            'completed'   => 'Completed',
            'in_progress' => 'In Progress',
            'canceled'    => 'Canceled',
        ],

        'priority' => [
            'high'   => 'High',
            'medium' => 'Medium',
            'low'    => 'Low',
        ],
    ],
    'financial-goal-transactions' => [
        'status' => [
            'pending'   => 'Pending',
            'completed' => 'Completed',
        ],

        'type'   => [
            'contribution' => 'Reservation',
            'usage' => 'Usage',
            'withdrawal'   => 'Release',
        ],
    ],
    'financial-goal-user-invites' => [
        'status' => [
            'pending'  => 'Pending',
            'accepted' => 'Accepted',
            'revoked'  => 'Revoked',
        ],
    ],
];
