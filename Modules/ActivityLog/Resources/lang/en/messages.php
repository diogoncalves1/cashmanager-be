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
            'invited_revoked'        => 'Invitation declined',
            'invited_revoked'        => 'Member removed',
            'user_role_updated'      => 'Member role updated',
            'user_leaved'            => 'User left',
        ],

        'messages' => [
            'account_created'        => 'Account created: :accountName',
            'account_updated'        => 'Account updated: :changes',
            'account_status_updated' => 'Status updated to :status',

            'transaction_added'      => ':type added on :date with the amount of :amount',
            'transaction_scheduled'  => ':type scheduled for :date with the amount of :amount',
            'transaction_updated'    => 'Transaction updated: :changes',
            'transaction_deleted'    => 'Transaction with amount :amount was deleted',
            'transaction_confirmed'  => 'Transaction with amount :amount scheduled for :date was confirmed',

            'user_invited'           => ':userName invited as :roleName',
            'user_joined'            => 'User :userName joined the account as :roleName',
            'invited_destroyed'      => 'Invitation for :userName deleted',
            'invited_revoked'        => ':userName was removed',
            'user_role_updated'      => 'Role of :userName was updated to :roleName',
            'user_leaved'            => ':userName left the account',
        ],
    ],

    // Debts
    'debt'           => [
        'titles'   => [
            'debt_created'        => 'Debt created',
            'debt_updated'        => 'Debt updated',
            'debt_status_updated' => 'Status updated',

            'payment_added'       => 'Payment added',
            'payment_scheduled'   => 'Payment scheduled',
            'payment_updated'     => 'Payment updated',
            'payment_deleted'     => 'Payment deleted',
            'payment_confirmed'   => 'Payment confirmed',

            'user_invited'        => 'User invited',
            'user_joined'         => 'New user',
            'invited_destroyed'   => 'Invitation deleted',
            'invited_revoked'     => 'Invitation declined',
            'invited_revoked'     => 'Member removed',
            'user_role_updated'   => 'Member role updated',
            'user_leaved'         => 'User left',
        ],

        'messages' => [
            'debt_created'        => 'Initial debt: :initialAmount',
            'debt_updated'        => 'Debt updated: :changes',
            'debt_status_updated' => 'Status updated to: :status',

            'payment_added'       => 'Payment added on :date with the amount of :amount, with an interest rate of :interest_rate%. It was a monthly payment: :is_monthly_payment',
            'payment_scheduled'   => 'Payment scheduled for :date with the amount of :amount, with an interest rate of :interest_rate%. It will be a monthly payment: :is_monthly_payment',
            'payment_updated'     => 'Payment updated: :changes',
            'payment_deleted'     => 'Payment with amount :amount on :date was deleted',
            'payment_confirmed'   => 'Payment with amount :amount scheduled for :date was confirmed',

            'user_invited'        => ':userName invited as :roleName',
            'user_joined'         => 'User :userName joined the debt as :roleName',
            'invited_destroyed'   => 'Invitation for :userName deleted',
            'invited_revoked'     => ':userName was removed',
            'user_role_updated'   => 'Role of :userName was updated to :roleName',
            'user_leaved'         => ':userName left the debt',
        ],
    ],

    // Financial Goals
    'financial_goal' => [
        'titles'   => [
            'goal_created'                    => 'Goal created',
            'goal_updated'                    => 'Goal updated',
            'goal_status_updated'             => 'Status updated',

            'financial_transaction_added'     => 'Transaction added',
            'financial_transaction_scheduled' => 'Transaction scheduled',
            'financial_transaction_updated'   => 'Transaction updated',
            'financial_transaction_deleted'   => 'Transaction deleted',
            'financial_transaction_confirmed' => 'Transaction confirmed',

            'user_invited'                    => 'User invited',
            'user_joined'                     => 'New user',
            'invited_destroyed'               => 'Invitation deleted',
            'invited_revoked'                 => 'Invitation declined',
            'invited_revoked'                 => 'Member removed',
            'user_role_updated'               => 'Member role updated',
            'user_leaved'                     => 'User left',
        ],

        'messages' => [
            'goal_created'                    => 'Initial goal: :initialTarget',
            'goal_updated'                    => 'Goal updated: :changes',
            'goal_status_updated'             => 'Status updated to: :status',

            'financial_transaction_added'     => ':type added on :date with the amount of :amount',
            'financial_transaction_scheduled' => ':type scheduled for :date with the amount of :amount',
            'financial_transaction_updated'   => 'Transaction updated: :changes',
            'financial_transaction_deleted'   => 'Transaction with amount :amount was deleted',
            'financial_transaction_confirmed' => 'Transaction with amount :amount scheduled for :date was confirmed',

            'user_invited'                    => ':userName invited as :roleName',
            'user_joined'                     => 'User :userName joined the financial goal as :roleName',
            'invited_destroyed'               => 'Invitation for :userName deleted',
            'invited_revoked'                 => ':userName was removed',
            'user_role_updated'               => 'Role of :userName was updated to :roleName',
            'user_leaved'                     => ':userName left the financial goal',
        ],
    ],

    'format-changes' => [
        'support' => ':field from ":old" to ":new"',
    ],
];
