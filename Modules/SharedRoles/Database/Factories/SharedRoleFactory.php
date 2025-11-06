<?php

namespace Modules\SharedRoles\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SharedRoles\Entities\SharedRole;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\SharedRoles\Entities\SharedRole>
 */
class SharedRoleFactory extends Factory
{
    protected $model = SharedRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ['en' => $this->faker->word()];

        return [
            'code' => $this->faker->unique()->word(),
            'name' => $name,
        ];
    }
}
