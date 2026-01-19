<?php
return [
    // Debts
    'debts'             => [
        'store'     => 'Debt :name added successfully!',
        'update'    => 'Debt :name updated successfully!',
        'destroy'   => 'Debt :name deleted successfully!',
        'mark-paid' => 'Debt :name marked paid successfully!',
        'reset'     => 'Debt :name reseted successfully!',

        'errors'    => [
            'store'     => 'Error trying to add debt.',
            'update'    => 'Error trying to update debt.',
            'destroy'   => 'Error trying to delete debt.',
            'mark-paid' => 'Error trying to mark debt paid.',
            'reset'     => 'Error trying to reset debt.',
        ],
    ],

    // Debt Users
    'debt-users'        => [
        'revokeUser'     => 'User :userName has been removed from debt :debtName.',
        'updateUserRole' => "User :userName's role has been updated for debt :debtName.",
        'leave'          => 'Left debt :debtName successfully.',

        'errors'         => [
            'revokeUser'     => 'Failed to remove the user from the debt.',
            'updateUserRole' => 'Failed to update the user role for the debt.',
            'leave'          => 'Failed to leave the debt.',
        ],
    ],

    // Debt User Invites
    'debt-user-invites' => [
        'invite'  => 'User :userName was successfully invited to debt :debtName.',
        'accept'  => 'Invitation to debt :name accepted successfully.',
        'destroy' => 'The invitation to the debt :debtName for the user :userName has been successfully deleted.',
        'revoke'  => 'Invitation to debt :debtName declined.',

        'errors'  => [
            'invite'  => 'Failed to invite user.',
            'accept'  => 'Failed to accept invitation.',
            'destroy' => 'Failed to delete invitation.',
            'revoke'  => 'Failed to decline invitation.',
        ],
    ],

    // Debt Payments
    'debt-payments'     => [
        'store'   => 'Debt payment added successfully!',
        'update'  => 'Debt payment updated successfully!',
        'destroy' => 'Debt payment deleted successfully!',
        'confirm' => 'Debt payment confirmed successfully!',

        'errors'  => [
            'store'   => 'Error trying to add debt payment.',
            'update'  => 'Error trying to update debt payment.',
            'destroy' => 'Error trying to delete debt payment.',
            'confirm' => 'Error trying to confirm debt payment.',
        ],
    ],

];
