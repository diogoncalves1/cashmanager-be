<?php
namespace Modules\Notification\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Notification\Entities\Notification;

class NotificationCacheService
{
    public function get(int $userId)
    {
        return Cache::remember(
            "notifications:feed:{$userId}",
            now()->addMinutes(5),
            function () use ($userId) {
                return Notification::where('user_id', $userId)
                    ->whereNull('archived_at')
                    ->latest()
                    ->take(20)
                    ->get();
            }
        );
    }

    public function getUnreadCount(int $userId): int
    {
        return Cache::remember(
            "notifications:unread:{$userId}",
            now()->addMinutes(5),
            function () use ($userId) {
                return Notification::where('user_id', $userId)
                    ->whereNull('read_at')
                    ->count();
            }
        );
    }

    public function forgetFeed(int $userId): void
    {
        Cache::forget("notifications:feed:{$userId}");
    }

    public function forgetUnread(int $userId): void
    {
        Cache::forget("notifications:unread:{$userId}");
    }
}
