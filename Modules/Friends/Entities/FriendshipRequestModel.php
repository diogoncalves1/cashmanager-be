<?php

namespace Modules\Friends\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Entities\User;

class FriendshipRequestModel extends Model
{
    use HasFactory;

    protected $table = 'friendship_requests';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['sender_id', 'receiver_id', 'status'];
    public $timestamps = true;

    protected static function newFactory()
    {
        return \Modules\Friends\Database\Factories\FriendshipRequestFactory::new();
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
