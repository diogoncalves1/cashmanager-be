<?php

return [

    'account'        => [
        'titles'   => [
            'account_created'        => 'Account created',
            'account_updated'        => 'Account updated',
            'account_status_updated' => 'Status updated',

            'transaction_added'      => 'Transaction added',
            'transaction_scheduled'  => 'Transaction scheduled',
            'transaction_updated'    => 'Transaction updated',
            'transaction_deleted'    => 'Transaction deleted',
            'transaction_confirmed'  => 'Transaction confirmed',

            'user_invited'           => 'User invited',
            'user_joined'            => 'New user',
            'invited_destroyed'      => 'Invitation deleted',
            'invited_revoked'        => 'Invitation revoked',
            'invited_revoked'        => 'Member removed',
            'user_role_updated'      => 'Member role updated',
            'user_leaved'            => 'User left',
        ],

        'messages' => [
            'account_created'        => 'Account created: :accountName',
            'account_updated'        => 'Account updated: :changes',
            'account_status_updated' => 'Status updated to :status',

            'transaction_added'      => ':type added on :date with an amount of :amount',
            'transaction_scheduled'  => ':type scheduled for :date with an amount of :amount',
            'transaction_updated'    => 'Transaction updated: :changes',
            'transaction_deleted'    => 'Transaction with amount :amount was deleted',
            'transaction_confirmed'  => 'Transaction with amount :amount scheduled for :date was confirmed',

            'user_invited'           => ':userName invited as :roleName',
            'user_joined'            => 'User :userName joined the account as :roleName',
            'invited_destroyed'      => 'Invitation for :userName deleted',
            'invited_revoked'        => ':userName was removed',
            'user_role_updated'      => ':userName role updated to :roleName',
            'user_leaved'            => ':userName left the account',
        ],
    ],

    // Financial Goals
    'financial_goal' => [
        'titles'   => [
            'goal_created'       => 'Goal created',
            'goal_paused'        => 'Goal canceled',
            'contribution_added' => 'Contribution added',
            'goal_reseted'       => 'Goal reset',
            'goal_completed'     => 'Goal completed',

            'user_invited'       => 'User invited',
            'user_joined'        => 'New user',
            'invited_destroyed'  => 'Invitation deleted',
            'invited_revoked'    => 'Invitation revoked',
            'invited_revoked'    => 'Member removed',
            'user_role_updated'  => 'Member role updated',
            'user_leaved'        => 'User left',
        ],

        'messages' => [
            'goal_created'       => 'Initial goal: :initialTarget',
            'contribution_added' => 'Contribution of :amount added from account :accountName',
            'goal_paused'        => 'The financial goal was canceled',
            'goal_reseted'       => 'The financial goal was reset to pending',
            'goal_completed'     => 'The financial goal was completed',

            'user_invited'       => ':userName invited as :roleName',
            'user_joined'        => 'User :userName joined the financial goal as :roleName',
            'invited_destroyed'  => 'Invitation for :userName deleted',
            'invited_revoked'    => ':userName was removed',
            'user_role_updated'  => ':userName role updated to :roleName',
            'user_leaved'        => ':userName left the financial goal',
        ],
    ],

    // Debts
    'debt'           => [
        'titles'   => [
            'debt_created'      => 'Debt created',

            'user_invited'      => 'User invited',
            'user_joined'       => 'New user',
            'invited_destroyed' => 'Invitation deleted',
            'invited_revoked'   => 'Invitation revoked',
            'invited_revoked'   => 'Member removed',
            'user_role_updated' => 'Member role updated',
            'user_leaved'       => 'User left',
        ],

        'messages' => [
            'debt_created'      => 'Initial debt: :initialAmount',

            'user_invited'      => ':userName invited as :roleName',
            'user_joined'       => 'User :userName joined the debt as :roleName',
            'invited_destroyed' => 'Invitation for :userName deleted',
            'invited_revoked'   => ':userName was removed',
            'user_role_updated' => ':userName role updated to :roleName',
            'user_leaved'       => ':userName left the debt',
        ],
    ],

    'format-changes' => [
        'support' => ':field from ":old" to ":new"',
    ],
];
