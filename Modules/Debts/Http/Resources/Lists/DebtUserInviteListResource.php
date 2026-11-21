<?php

namespace Modules\Debts\Http\Resources\Lists;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Debts\Http\Resources\DebtResource;
use Modules\SharedRoles\Http\Resources\SharedRoleResource;
use Modules\User\Http\Resources\UserShareResource;

class DebtUserInviteListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        $user = $request->user();
        $data = $row->attributesToArray();
        $data['receiver'] = new UserShareResource($row->user);
        $data['sender'] = new UserShareResource($row->sender);
        $data['subject'] = new DebtResource($row->debt);
        $data['sharedRole'] = new SharedRoleResource($row->sharedRole);
        $data['createdAt'] = $this->createdAt($row, $request);
        $data['statusTranslated'] = __('financialgoal::attributes.financial-goal-user-invites.status.'.$row->status);
        foreach (['user_id'] as $field) {
            unset($data[$field]);
        }
        foreach (['invited_by_id'] as $field) {
            unset($data[$field]);
        }
        foreach (['shared_role_id'] as $field) {
            unset($data[$field]);
        }
        foreach (['debt_id'] as $field) {
            unset($data[$field]);
        }
        foreach (['created_at'] as $field) {
            unset($data[$field]);
        }
        foreach (['updated_at'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    private function createdAt($row, Request $request): mixed
    {
        $user = $request->user();

        $date = new Carbon($row->created_at);

        return $date->format('Y-m-d');

    }
}
