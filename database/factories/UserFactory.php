<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fullName = fake()->name();
        [$firstName, $lastName] = $this->splitName($fullName);

        return [
            'name' => $firstName,
            'last_name' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => $this->defaultRoleId(),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    private function defaultRoleId(): int
    {
        return Role::query()->firstOrCreate(['rol_nombre' => Role::CLIENTE])->getKey();
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2) ?: [];

        $firstName = $parts[0] ?? $fullName;
        $lastName = $parts[1] ?? $firstName;

        return [$firstName, $lastName];
    }
}
