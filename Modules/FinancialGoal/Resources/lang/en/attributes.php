<?php

return [
    'financial-goals'             => [
        'status'   => [
            'completed'   => 'Paid',
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
            'contribution' => 'Contribution',
            'withdrawal'   => 'Withdrawal',
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
