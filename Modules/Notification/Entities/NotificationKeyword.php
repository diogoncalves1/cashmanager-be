<?php
namespace Modules\Notification\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Notification\Database\Factories\NotificationKeywordFactory;

class NotificationKeyword extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['keyword', 'description'];

    protected function notificationTypes()
    {
        return $this->belongsToMany(NotificationType::class, 'notification_type_keywords');
    }

    // protected static function newFactory(): NotificationKeywordFactory
    // {
    //     // return NotificationKeywordFactory::new();
    // }
}
