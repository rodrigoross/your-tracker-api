<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function users_can_be_authenticated()
    {
        $user = User::factory()
            ->create([
                'password' => bcrypt($password = $this->faker->password(8))
            ]);

        $this->postJson(route('api.login'),
            [
                'email' => $user->email,
                'password' => $password,
                'device_name' => 'Test Device',
            ])
            ->assertOk()
            ->assertJsonStructure([
                'user',
                'plainTextToken',
            ]);
    }

    /** @test */
    public function it_should_validate_user_exists()
    {
        $this->postJson(route('api.login'),
            [
                'email' => $this->faker->safeEmail(),
                'password' => $this->faker->password(8),
                'device_name' => "Test Device",
            ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => 'The provided credentials are incorrect.'
            ]);
    }

    /** @test */
    public function it_should_validate_incorrect_password()
    {
        $user = User::factory()->create();

        $this->postJson(route('api.login'),
            [
                'email' => $user->email,
                'password' => $this->faker->password(8),
                'device_name' => "Test Device",
            ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => 'The provided credentials are incorrect.'
            ]);
    }
}
