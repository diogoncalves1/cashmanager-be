<?php

namespace Modules\Category\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Category\Entities\Category;
use Modules\User\Entities\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Category\Entities\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = $this->faker->boolean(20) ? User::pluck('id')->random() : null;
        $default = $userId ? 0 : 1;

        return [
            'name' => ["en" =>  $this->faker->word()],
            'type' => $this->faker->randomElement(['revenue', 'expense']),
            'icon' => $this->faker->word(),
            'color' => $this->faker->safeColorName(),
            'default' => $default,
            'parent_id' => $this->faker->boolean(10) ? Category::pluck('id')->random('id') : null,
            'user_id' => $userId,
        ];
    }
}
