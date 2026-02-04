<?php
namespace Modules\ActivityLog\Repositories;

use Modules\ActivityLog\Entities\ActivityLog;
use Modules\ActivityLog\Enums\ActivityTypeEnum;
use Modules\ActivityLog\Exceptions\ActivityLogTypeInvalid;

class ActivityLogRepository
{
    public function storeActivity(int $subjectId, string $userId, string $type, ?array $metadata = null)
    {
        $this->testType($type);

        $data = [
            'subject_id' => $subjectId,
            'user_id'    => $userId,
            'type'       => $type,
            'metadata'   => $metadata,
        ];

        return ActivityLog::create($data);
    }

    public function destroyByType(int $subjectId, string $type)
    {
        $this->testType($type);

        return ActivityLog::where("subject_id", $subjectId)->where('type', $type)->delete();
    }

    private function testType(string $type)
    {
        if (ActivityTypeEnum::tryFrom($type)) {
            throw new ActivityLogTypeInvalid();
        }
    }
}
