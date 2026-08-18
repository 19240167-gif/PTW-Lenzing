<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $name     = $this->faker->name();
        $username = strtolower(str_replace(" ", ".", $name));

        return [
            "name"           => $name,
            "employee_id"    => strtoupper($this->faker->bothify("EMP-####")),
            "domain"         => "LAGGRF",
            "username"       => $username,
            "upn"            => $username . "@pt-spv.com",
            "email"          => $username . "@pt-spv.com",
            "position"       => $this->faker->jobTitle(),
            "is_active"      => true,
            "is_global_admin"=> false,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn() => ["is_global_admin" => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn() => ["is_active" => false]);
    }
}
