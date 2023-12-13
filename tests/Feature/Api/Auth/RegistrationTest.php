<?php

namespace Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_register_new_users()
    {
        $this->postJson(route('api.register'),
            $data = [
                'name' => $this->faker->name(),
                'email' => $this->faker->safeEmail(),
                'password' => "password",
                'password_confirmation' => "password",
                'device_name' => "Test Device",
            ])
            ->assertCreated()
            ->assertJsonStructure([
                'user',
                'plainTextToken',
            ])
            ->assertJsonFragment([
                'user' => [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'email_verified_at' => null,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'email_verified_at' => null,
        ]);
    }

    /** @test */
    public function it_should_ensure_email_is_unique()
    {
        $user = User::factory()->create();

        $this->postJson(route('api.register'),
            $data = [
                'name' => $this->faker->name(),
                'email' => $user->email,
                'password' => $this->faker->password(8),
                'device_name' => "Test Device",
            ])
            ->assertJsonValidationErrors([
                'email' => __('validation.unique', ['attribute' => 'email']),
            ])
            ->assertUnprocessable();

        $this->assertDatabaseCount('users', 1);
    }

    /** @test */
    public function it_should_ensure_that_password_is_filled_for_registration()
    {
        $this->postJson(route('api.register'),
            $data = [
                'email' => $this->faker->safeEmail(),
                'name' => $this->faker->name(),
                'password_confirmation' => 'P@ssword1'
            ])
            ->assertJsonValidationErrors([
                'password' => __('validation.required', ['attribute' => 'password']),
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('users', [
            'email' => $data['email'],
        ]);
    }

    /** @test */
    public function it_should_ensure_that_password_was_confirmed_for_registration()
    {
        $this->postJson(route('api.register'),
            $data = [
                'email' => $this->faker->safeEmail(),
                'name' => $this->faker->name(),
                'password' => 'P@ssword1'
            ])
            ->assertJsonValidationErrors([
                'password' => __('validation.confirmed', ['attribute' => 'password']),
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('users', [
            'email' => $data['email'],
        ]);
    }

    /** @test */
    public function it_should_check_password_has_at_least_eight_chars_for_registration()
    {
        $this->postJson(route('api.register'),
            $data = [
                'email' => $this->faker->safeEmail(),
                'name' => $this->faker->name(),
                'password' => 'P@ss'
            ])
            ->assertJsonValidationErrors([
                'password' => __('validation.min', ['attribute' => 'password', 'min' => 8]),
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('users', [
            'email' => $data['email'],
        ]);
    }
}
