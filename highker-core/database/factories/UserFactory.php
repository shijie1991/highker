<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Factories;

use HighKer\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
