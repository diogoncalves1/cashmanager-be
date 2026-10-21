<?php
namespace Modules\Notification\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Entities\User;

// use Modules\Notification\Database\Factories\NotificationFactory;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'type_code', 'data', 'read_at', 'archived_at'];

    protected $casts = [
        'data'        => 'array',
        'read_at'     => 'datetime',
        'deleted_at'  => 'datetime',
        'archived_at' => 'datetime',
    ];

    protected function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function notificationType()
    {
        return $this->belongsTo(NotificationType::class, 'type_code', 'code');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // protected static function newFactory(): NotificationFactory
    // {
    //     // return NotificationFactory::new();
    // }
}
