<?php
namespace Modules\ActivityLog\DataTables;

use Carbon\Carbon;
use Modules\ActivityLog\Core\ActivityLogMessageResolver;
use Modules\ActivityLog\Entities\ActivityLog;
use Modules\User\Http\Resources\UserShareResource;
use Modules\User\Repositories\UserRepository;
use Yajra\DataTables\Services\DataTable;

class ActivityLogDataTable extends DataTable
{
    public string $id;
    public string $type;

    public function __construct(protected UserRepository $userRepo,
        protected ActivityLogMessageResolver $messageResolver) {
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('user', fn(ActivityLog $activity) => new UserShareResource($activity->user))
            ->addColumn('title', fn(ActivityLog $activity) => __('activitylog::messages.' . $this->type . '.titles.' . $activity->metadata['type']))
            ->addColumn('message', function (ActivityLog $activity) {
                $messageArr = $this->messageResolver->resolve($this->type, $activity->metadata);

                return __($messageArr['key'], $messageArr['params']);
            })
            ->addColumn('createdAt', fn(ActivityLog $activity) => new Carbon($activity->created_at)->format('Y-m-d'))
            ->removeColumn('user_id')
            ->removeColumn('metadata')
            ->removeColumn('subject_id')
            ->removeColumn('type')
            ->removeColumn('created_at')
            ->removeColumn('id')
            ->removeColumn('updated_at')
            ->rawColumns(['metadata', 'user']);

    }

    public function query(ActivityLog $model)
    {
        return $model->newQuery()
            ->where("subject_id", $this->id)
            ->where("type", $this->type)
            ->orderBy("created_at", 'desc')
            ->orderBy('id', 'desc');
    }
}
