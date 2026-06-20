<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->numerify('09########'), // يولد رقم جوال فريد تبدأ بـ 05
            'password' => bcrypt('password'), // كلمة المرور الافتراضية للتست
            'is_active' => true,
            'image_url' => $this->faker->imageUrl(200, 200, 'people'),
            'remember_token' => Str::random(10),
            'email_verified_at' => $this->faker->boolean(70) ? now() : null,
            'last_login_at' => $this->faker->dateTimeThisMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
