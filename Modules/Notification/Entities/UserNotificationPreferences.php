<?php
namespace Modules\Notification\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

// use Modules\Notification\Database\Factories\UserNotificationPreferencesFactory;

class UserNotificationPreferences extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'type_id', 'in_app', 'email'];

    protected function user()
    {
        return $this->belogTo(User::class);
    }

    protected function notificationType()
    {
        return $this->belongsTo(NotificationType::class);
    }

    // protected static function newFactory(): UserNotificationPreferencesFactory
    // {
    //     // return UserNotificationPreferencesFactory::new();
    // }
}
