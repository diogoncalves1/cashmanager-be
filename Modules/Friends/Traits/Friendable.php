<?php

namespace Modules\Friends\Traits;

use Modules\Friends\Repositories\FriendshipRepository;

trait Friendable
{
    public function sendFriendRequest(string $id)
    {
        return app(FriendshipRepository::class)->send($id);
    }

    public function acceptFriendRequest(string $id)
    {
        return app(FriendshipRepository::class)->accept($id);
    }

    public function declineFriendRequest(string $id)
    {
        return app(FriendshipRepository::class)->decline($id);
    }

    public function removeRelation(string $id)
    {
        return app(FriendshipRepository::class)->remove($id);
    }

    public function blockUser(string $id)
    {
        return app(FriendshipRepository::class)->block($id);
    }
}
