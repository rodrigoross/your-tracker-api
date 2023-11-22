<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Helper function to create a signed in user
     *
     * @param User | null $user
     * @return User $usuario
     */
    protected function signIn(?User $user = null): User
    {
        $user = $user ?: User::factory()->create();

        $this->actingAs($user);

        return $user;
    }
}
