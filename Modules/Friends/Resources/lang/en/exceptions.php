<?php

return [
    'friendships' => [
        'alreadyFriendsException' => 'Friendship already existing between users.',
        'selfBlockNotAllowedException' => "You can't block yourself.",
        'userBlockedException' => 'User blocked.',
        'selfFriendshipException' => 'Unable to send, accept or reject any request to yourself.',
        'friendshipNotFoundException' => 'This friendship does not exist or has already been removed.',
        'userNotBlockedException' => 'This user is not blocked.'
    ],

    'friendships-requests' => [
        'friendRequestAlreadySentException' => 'Friend request already sent.',
        'friendRequestLimitExceededException' => "You've reached the maximum number of friend requests allowed. Please try again later.",
        'friendRequestNotFoundException' => 'Friend request not found.',
        'senderCannotAcceptFriendRequestException' => 'The sender cannot accept their own friend request.'
    ]
];
