<?php

namespace Modules\SharedRoles\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SharedRoles\Entities\SharedPermission;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\SharedRoles\Entities\SharedPermission>
 */
class SharedPermissionFactory extends Factory
{
    protected $model = SharedPermission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'category' => $this->faker->word(),
            'code' => $this->faker->unique()->word()
        ];
    }
}
