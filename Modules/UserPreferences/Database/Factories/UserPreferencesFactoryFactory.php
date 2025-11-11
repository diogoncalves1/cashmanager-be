<?php

namespace Modules\UserPreferences\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Currency\Entities\Currency;
use Modules\User\Entities\User;
use Modules\UserPreferences\Entities\UserPrefence;

class UserPreferencesFactoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = UserPrefence::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->unique()->randomElement(User::pluck('id')),
            'currency_id' => Currency::pluck('id')->random(),
            'lang' => $this->faker->randomElement(['en', 'pt'])
        ];
    }
}
