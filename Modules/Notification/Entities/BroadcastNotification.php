<?php
namespace Modules\Notification\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Modules\User\Entities\User;

// use Modules\Notification\Database\Factories\BroadcastNotificationFactory;

class BroadcastNotification extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['type_code', 'created_by_id', 'send_email'];

    protected function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    protected function notificationType()
    {
        return $this->belongsTo(NotificationType::class, 'type_code', 'code');
    }

    // protected static function newFactory(): BroadcastNotificationFactory
    // {
    //     // return BroadcastNotificationFactory::new();
    // }
}
