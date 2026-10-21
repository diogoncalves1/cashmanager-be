<?php

return [
    'friendship-requests' => [
        'send' => 'Friend request successfully sent to :name.',
        'accept' => 'Friend request from :name accepted successfully.',
        'decline' => 'Friend request from :name declined successfully.',
        'cancel' => 'Friend request to :name cancelled successfully.',
        'errors' => [
            'send' => 'Unable to send the friend request.',
            'accept' => 'Error while trying to accept friend request.',
            'decline' => 'Unable to decline the friend request.',
            'cancel' => 'Unable to cancel the friend request.',
        ]
    ],

    'friendships' => [
        'remove' => 'Friend removed successfully.',
        'block' => ':name blocked successfully.',
        'unblock' => ':name unblocked successfully.',
        'errors' => [
            'remove' => 'Unable to remove the friend.',
            'block' => 'Unable to block the user.',
            'unblock' => 'Unable to unblock the user.'
        ]
    ]
];
