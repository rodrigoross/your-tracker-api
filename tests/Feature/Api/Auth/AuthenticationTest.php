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
            $data = [
                'email' => $user->email,
                'password' => $password,
                'device_name' => 'Test Device',
                'device_token' => preg_replace('/[^a-z0-9]/i', '', $this->faker->text(50)),
            ])
            ->assertOk()
            ->assertJsonStructure([
                'user',
                'plainTextToken',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => $data['device_token'],
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
                'device_token' => preg_replace('/[^a-z0-9]/i', '', $this->faker->text(50)),
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
                'device_token' => preg_replace('/[^a-z0-9]/i', '', $this->faker->text(50)),
            ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => 'The provided credentials are incorrect.'
            ]);
    }

    /** @test */
    public function it_should_ensure_device_token_is_present()
    {
        $user = User::factory()->create();

        $this->postJson(route('api.login'),
            [
                'email' => $user->email,
                'password' => $this->faker->password(8),
                'device_name' => "Test Device",
            ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'device_token' => __('validation.required', ['attribute' => 'device token'])
            ]);
    }
}
