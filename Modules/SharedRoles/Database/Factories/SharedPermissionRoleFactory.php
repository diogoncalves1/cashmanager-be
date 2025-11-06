<?php

namespace Modules\SharedRoles\Database\Factories;

use Modules\SharedRoles\Entities\SharedPermission;
use Modules\SharedRoles\Entities\SharedRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SharedRoles\Entities\SharedPermissionRole;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\SharedRoles\Entities\SharedPermissionRole>
 */
class SharedPermissionRoleFactory extends Factory
{
    protected $model = SharedPermissionRole::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shared_role_id' => SharedRole::pluck('id')->random(),
            'shared_permission_id' => SharedPermission::pluck('id')->random()
        ];
    }
}
