<?php

namespace Modules\ActivityLog\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Modules\ActivityLog\Entities\ActivityLog;
use Modules\ActivityLog\Filters\ActivityLogFilter;
use Modules\ActivityLog\Http\Resources\Lists\ActivityLogListResource;
use Modules\ActivityLog\Searches\ActivityLogSearch;
use Modules\User\Entities\User;

class ActivityLogPaginate extends ModulePaginate
{
    public const RESOURCE = ActivityLogListResource::class;

    protected array $allowedOrderFields = ['id' => 'activity_logs.id', 'created_at' => 'activity_logs.created_at'];

    protected string $defaultOrderField = 'activity_logs.id';

    protected string $defaultSort = 'desc';

    public string $id;

    public string $type;

    public function __construct(ActivityLogFilter $filter, ActivityLogSearch $search)
    {
        parent::__construct($filter, $search);
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return ActivityLog::query()->where('subject_id', $this->id)->where('type', $this->type)->with('user');
    }
}
