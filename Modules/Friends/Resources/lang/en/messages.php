<?php

return [
    'friendship-requests' => [
        'send' => 'Friend request successfully sent to :name.',
        'accept' => ':name friend request successfully accepted.',
        'decline' => ':name friend request was successfully rejected.',
        'errors' => [
            'send' => 'Error sending friend request',
            'accept' => 'Error while trying to accept friend request.',
            'decline' => 'Error rejecting friend request'
        ]
    ],

    'friendships' => [
        'remove' => 'Friendship successfully broken.',
        'block' => ':name blocked successfully.',
        'unblock' => ':name unblocked successfully.',
        'errors' => [
            'remove' => 'Error to try broke friendship.',
            'block' => 'Error to try block user.',
            'unblock' => 'Error to try unblock user.'
        ]
    ]
];
