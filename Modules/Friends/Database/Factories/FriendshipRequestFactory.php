<?php

namespace Modules\Friends\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\User\Entities\User;

class FriendshipRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Friends\Entities\FriendshipRequestModel::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sender_id' => User::pluck('id')->random(),
            'receiver_id' => User::pluck('id')->random(),
            'status' => $this->faker->randomElement(['pending', 'declined'])
        ];
    }
}
