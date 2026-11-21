<?php

namespace Modules\ActivityLog\Http\Resources\Lists;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ActivityLog\Core\ActivityLogMessageResolver;
use Modules\User\Http\Resources\UserShareResource;

class ActivityLogListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $user = $request->user();
        $data = $row->attributesToArray();
        $data['user'] = new UserShareResource($row->user);
        $data['title'] = __('activitylog::messages.'.$this->type.'.titles.'.$row->metadata['type']);
        $data['message'] = $this->message($row, $request);
        $data['createdAt'] = $this->createdAt($row, $request);
        foreach (['user_id'] as $field) {
            unset($data[$field]);
        }
        foreach (['metadata'] as $field) {
            unset($data[$field]);
        }
        foreach (['subject_id'] as $field) {
            unset($data[$field]);
        }
        foreach (['type'] as $field) {
            unset($data[$field]);
        }
        foreach (['created_at'] as $field) {
            unset($data[$field]);
        }
        foreach (['id'] as $field) {
            unset($data[$field]);
        }
        foreach (['updated_at'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    private function message($row, Request $request): mixed
    {
        $user = $request->user();

        $messageArr = app(ActivityLogMessageResolver::class)->resolve($row->type, $row->metadata);

        return __($messageArr['key'], $messageArr['params']);

    }

    private function createdAt($row, Request $request): mixed
    {
        $user = $request->user();

        $date = new Carbon($row->created_at);

        return $date->format('Y-m-d H:i:s');

    }
}
