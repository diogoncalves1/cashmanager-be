<?php

namespace Modules\Friends\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class Friendship extends Model
{
    /** @use HasFactory<\Modules\Friends\Database\Factories\FriendshipFactory> */
    use HasFactory;

    protected $table = "friendships";
    protected $fillable = ['sender_id', 'receiver_id', 'status'];

    protected static function newFactory()
    {
        return \Modules\Friends\Database\Factories\FriendshipFactory::new();
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }
    public function receiver()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSender($query, $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    public function scopeReceiver($query, $receiverId)
    {
        return $query->where('receiver_id', $receiverId);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
