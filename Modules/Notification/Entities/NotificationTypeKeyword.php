<?php
namespace Modules\Notification\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Notification\Database\Factories\NotificationTypeKeywordFactory;

class NotificationTypeKeyword extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['notification_type_id', 'notification_keyword_id'];

    protected function notificationType()
    {
        return $this->belongsTo(NotificationType::class, 'notification_type_id', 'id');
    }
    protected function notificationKeyword()
    {
        return $this->belongsTo(NotificationKeyword::class, 'notification_keyword_id', 'id');
    }

    // protected static function newFactory(): NotificationTypeKeywordFactory
    // {
    //     // return NotificationFactory::new();
    // }
}
