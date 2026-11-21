<?php

namespace Modules\Notification\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\Notification\Entities\BroadcastNotification;
use Modules\Notification\Entities\Notification;
use Modules\Notification\Filters\NotificationFilter;
use Modules\Notification\Http\Resources\NotificationResource;
use Modules\Notification\Searches\NotificationSearch;
use Modules\User\Entities\User;

class NotificationPaginate extends ModulePaginate
{
    public const RESOURCE = NotificationResource::class;

    protected array $allowedOrderFields = ['created_at' => 'notifications.created_at'];

    protected string $defaultOrderField = 'notifications.created_at';

    protected string $defaultSort = 'desc';

    protected array $tieBreakers = ['notifications.source', 'notifications.id'];

    public function __construct(NotificationFilter $filter, NotificationSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        $personal = Notification::query()->where('user_id', $user->id)->whereNull('archived_at')
            ->select(['id', 'user_id', 'data', 'type_code', 'created_at', 'read_at'])->selectRaw("'personal' as source");
        $broadcast = BroadcastNotification::query()
            ->selectRaw('id, NULL as user_id, NULL as data, type_code, created_at, NULL as read_at, \'broadcast\' as source');

        return Notification::query()->withoutGlobalScopes()->fromSub($personal->unionAll($broadcast), 'notifications')->with('notificationType.keywords');
    }
}
