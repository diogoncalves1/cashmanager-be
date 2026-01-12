<?php
return [
    // Financial goals
    'financial-goals'             => [
        'store'    => 'Financial goal :name added successfully!',
        'update'   => 'Financial goal :name updated successfully!',
        'destroy'  => 'Financial goal :name deleted successfully!',
        'cancel'   => 'Financial goal :name canceled successfully!',
        'complete' => 'Financial goal :name completed successfully!',
        'reset'    => 'Financial goal :name in progress again!',

        // Errors
        'errors'   => [

        ],
    ],

    // Financial goal users
    'financial-goal-users'        => [
        'revokeUser'     => 'User :userName has been removed from finacial goal :goalName.',
        'updateUserRole' => "User :userName's role has been updated for finacial goal :goalName.",
        'leave'          => 'Left finacial goal :goalName successfully.',

        'errors'         => [
            'revokeUser'     => 'Failed to remove the user from the finacial goal.',
            'updateUserRole' => 'Failed to update the user role for the finacial goal.',
            'leave'          => 'Failed to leave the finacial goal.',
        ],
    ],

    // Financial goal invites
    'financial-goal-user-invites' => [
        'invite'  => 'User :userName was successfully invited to financial goal :goalName.',
        'accept'  => 'Invitation to finacial goal :name accepted successfully.',
        'destroy' => 'The invitation to the finacial goal :goalName for the user :userName has been successfully deleted.',
        'revoke'  => 'Invitation to finacial goal :goalName declined.',

        'errors'  => [
            'invite'  => 'Failed to invite user.',
            'accept'  => 'Failed to accept invitation.',
            'destroy' => 'Failed to delete invitation.',
            'revoke'  => 'Failed to decline invitation.',
        ],
    ],

    'financial-goal-transactions' => [
        'store'   => 'Financial goal transaction added to financial goal :name successfully!',
        'update'  => 'Financial goal transaction updated to financial goal :name successfully!',
        'destroy' => 'Financial goal transaction deleted to financial goal :name successfully!',
        'confirm' => 'Financial goal transaction confirmed to financial goal :name successfully!',
    ],
];
