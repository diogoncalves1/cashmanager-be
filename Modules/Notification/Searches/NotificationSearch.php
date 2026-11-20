<?php

namespace Modules\Notification\Searches;

use App\Searches\ModuleSearch;

class NotificationSearch extends ModuleSearch
{
    protected array $fields = [];

    protected array $relations = ['notificationType' => ['notification_types.title', 'notification_types.message']];
}
