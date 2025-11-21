<?php
return [
    // Accounts
    'accounts'             => [
        'store'   => 'Account :name added successfully!',
        'update'  => 'Account :name updated successfully!',
        'destroy' => 'Account :name deleted successfully!',

        'errors'  => [
            'store'   => 'Error trying to add account.',
            'update'  => 'Error trying to update account.',
            'destroy' => 'Error trying to delete account.',
        ],
    ],

    // Account Users
    'account-users'        => [
        'revokeUser'     => 'User :userName has been removed from account :accountName.',
        'updateUserRole' => "User :userName's role has been updated for account :accountName.",
        'leave'          => 'Left account :accountName successfully.',

        'errors'         => [
            'revokeUser'     => 'Failed to remove the user from the account.',
            'updateUserRole' => 'Failed to update the user role for the account.',
            'leave'          => 'Failed to leave the account.',
        ],
    ],

    // Account User Invites
    'account-user-invites' => [
        'invite'  => 'User :userName was successfully invited to account :accountName.',
        'accept'  => 'Invitation to account :name accepted successfully.',
        'destroy' => 'The invitation to the account :accountName for the user :userName has been successfully deleted.',
        'revoke'  => 'Invitation to account :accountName declined.',

        'errors'  => [
            'invite'  => 'Failed to invite user.',
            'accept'  => 'Failed to accept invitation.',
            'destroy' => 'Failed to delete invitation.',
            'revoke'  => 'Failed to decline invitation.',
        ],
    ],

    // Transaction
    'transactions'         => [
        'store'   => 'Transaction added successfully!',
        'update'  => 'Transaction updated successfully!',
        'destroy' => 'Transaction deleted successfully!',
        'confirm' => 'Transaction confirmed successfully!',

        'errors'  => [
            'store'   => 'Error trying to add transaction.',
            'update'  => 'Error trying to update transaction.',
            'destroy' => 'Error trying to delete transaction.',
            'confirm' => 'Error trying to confirm transaction.',
        ],
    ],

];
