<?php
namespace Modules\Notification\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Notification\Database\Factories\NotificationTypeFactory;

class NotificationType extends Model
{
    use HasFactory;

    protected $table = 'notification_types';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['code', 'title', 'message', 'mail_subject', 'mail_message', 'mail_signature', 'pathname', 'is_broadcast'];

    protected $casts = [
        'title'          => 'array',
        'message'        => 'array',
        'mail_message'   => 'array',
        'mail_signature' => 'array',
        'mail_subject'   => 'array',
    ];

    public function keywords()
    {
        return $this->belongsToMany(NotificationKeyword::class, 'notification_type_keywords', 'notification_type_id', 'notification_keyword_id');
    }

    // protected static function newFactory(): NotificationTypeFactory
    // {
    //     // return NotificationTypeFactory::new();
    // }
}
