<?php

return [
    'shareable' => [
        'alreadyRelationException'            => 'Relation already existing.',
        'creatorCantLeaveException'           => 'The account creator cannot leave the account.',
        'creatorInviteException'              => 'Cannot invite a user with the creator role.',
        'creatorRevokeException'              => 'Cannot remove a user with the creator role from the account.',
        'inviteAlreadySentException'          => 'Invite already sent.',
        "inviteNotFoundException"             => "Invite not found.",
        'invitesLimitExceededException'       => "You've reached the maximum number of invites allowed. Please try again later.",
        'inviteUserNotAllowedException'       => 'User invitation is not allowed.',
        'relationNotExistsException'          => 'The specified relation does not exist.',
        'selfInviteException'                 => 'Unable to send, accept or reject any invite to yourself.',
        'selfRoleUpdateNotAllowedException'   => 'You are not allowed to update your own role in the account.',
        'unauthorizedDestroyInviteException'  => 'You are not authorized to cancel this invite.',
        'unauthorizedRevokeUserException'     => 'You are not authorized to revoke this user.',
        "unauthorizedUpdateUserRoleException" => "You do not have permission to update the user role.",
    ],
];
