<?php

namespace Modules\Permission\Database\Factories;

use Modules\Permission\Entities\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Permission\Entities\Permission;
use Modules\Permission\Entities\RolePermission;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RolePermission>
 */
class RolePermissionFactory extends Factory
{
    protected $model = RolePermission::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => Role::pluck('id')->random(),
            'permission_id' => Permission::pluck('id')->random()
        ];
    }
}
