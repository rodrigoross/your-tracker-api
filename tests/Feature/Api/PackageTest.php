<?php

namespace Tests\Feature\Api;

use App\Enums\PackageIcon;
use App\Models\Event;
use App\Models\Package;
use App\Models\User;
use App\Services\Tracker\Contracts\TrackerServiceInterface;
use App\Services\Tracker\Entities\Tracker;
use App\Services\Tracker\Exceptions\PackageIsNotPostedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(TrackerServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('track')
                ->withArgs(['NL718729417BR'])
                ->andReturn(new Tracker(
                    'NL718729417BR',
                    'rd',
                    '2022-01-01T19:32:00.000000Z',
                    events: [
                        [
                            'data' => '01/01/2022',
                            'hora' => '00:00',
                            'local' => 'São Paulo - SP',
                            'status' => 'Objeto postado',
                            'subStatus' => [],
                        ]
                    ],
                ));
        });
    }

    /** @test */
    public function only_registered_users_can_create_packages()
    {
        $this->postJson(route('api.v1.packages.store'), $data = [
            'code' => 'NL718729417BR',
        ])->assertForbidden();

        $this->assertDatabaseMissing('packages', $data);
    }

    /** @test */
    public function guest_users_can_not_list_favorited_packages()
    {
        $this->getJson(route('api.v1.packages.index'))->assertForbidden();
    }

    /** @test */
    public function users_can_favorite_a_package()
    {
        $user = $this->signIn();

        $this->postJson(route('api.v1.packages.store'), $data = [
            'code' => 'NL718729417BR',
        ])->assertCreated()
            ->assertJsonStructure([
                'code',
                'icon',
                'alias',
                'lastEventAt',
                'events',
            ]);

        $this->assertDatabaseCount('packages', 1)
            ->assertDatabaseHas('packages', $data);

        $this->assertDatabaseHas('package_user', [
            'user_id' => $user->id,
            'icon' => 'default',
            'alias' => 'NL718729417BR',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseHas('events', [
                'datetime' => '2022-01-01 00:00:00',
                'status' => 'POSTED',
                'message' => 'Objeto postado',
            ]);
    }

    /** @test */
    public function users_can_not_favorite_the_package_more_than_one_time()
    {
        $user = $this->signIn();

        $package = Package::factory()
            ->has(
                Event::factory()->count(5)
            )->create();

        $user->favorite($package);

        $this->postJson(route('api.v1.packages.store'), [
            'code' => $package->code,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'code' => __('validation.unique', ['attribute' => 'code']),
            ]);

        $this->assertDatabaseCount('packages', 1);
        $this->assertDatabaseCount('package_user', 1);
    }

    /** @test */
    public function users_can_view_package_data()
    {
        $user = $this->signIn();

        $package = Package::factory()
            ->has(
                Event::factory()->count(5)
            )->create();

        $user->favorite($package);

        $this->getJson(route('api.v1.packages.show', $package->code))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'code',
                    'icon',
                    'alias',
                    'lastEventAt',
                    'events',
                ]
            );
    }

    /** @test */
    public function users_can_not_view_another_users_package()
    {
        $user = User::factory()->create();

        $package = Package::factory()
            ->has(
                Event::factory()->count(5)
            )->create();

        $user->favorite($package);

        $this->signIn();

        $this->getJson(route('api.v1.packages.show', $package->code))
            ->assertForbidden()
            ->assertJsonFragment([
                'message' => __('You do not have access to this package, favorite it first'),
            ]);
    }


    /** @test */
    public function should_notify_users_package_are_not_posted()
    {
        $this->mock(TrackerServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('track')
                ->withArgs(['ABCDEFGHIJKL12'])
                ->andThrow(new PackageIsnotPostedException);
        });

        $this->signIn();

        $this->postJson(route('api.v1.packages.store', ['code' => 'ABCDEFGHIJKL12']))
            ->assertNotFound()
            ->assertJsonFragment([
                'message' => __('Package not found or isn\'t posted'),
            ]);
    }

    /** @test */
    public function it_should_ensure_a_code_is_provided()
    {
        $this->signIn();

        $this->postJson(route('api.v1.packages.store'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'code' => __('validation.required', ['attribute' => 'code']),
            ]);

        $this->assertDatabaseCount('packages', 0);
    }

    /** @test */
    public function users_can_list_favorited_packages()
    {
        $user = $this->signIn();

        $packages = Package::factory()
            ->count(10)
            ->has(
                Event::factory()->count(2)
            )->create();

        $packages->take(5)->each(fn($package) => $user->favorite($package));

        $res = $this->getJson(route('api.v1.packages.index'));
    }

    /** @test */
    public function users_can_remove_packages_from_favorites()
    {
        $user = $this->signIn();

        $package = Package::factory()->create();

        $user->favorite($package);

        $this->deleteJson(route('api.v1.packages.destroy', $package->code))
            ->assertNoContent();

        $this->assertDatabaseMissing('package_user', [
            'package_id' => $package->id,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function users_can_update_package_icon()
    {
        $user = $this->signIn();

        $package = Package::factory()->create();

        $user->favorite($package);

        $this->patchJson(route('api.v1.packages.update', $package->code), $data = [
            'icon' => PackageIcon::random()
        ])->assertOk();

        $this->assertDatabaseHas('package_user', [
            'package_id' => $package->id,
            'user_id' => $user->id,
            'icon' => $data['icon']
        ]);
    }

    /** @test */
    public function users_can_update_package_alias()
    {
        $user = $this->signIn();

        $package = Package::factory()->create();

        $user->favorite($package);

        $this->patchJson(route('api.v1.packages.update', $package->code), $data = [
            'alias' => $this->faker->word(),
        ])->assertOk();

        $this->assertDatabaseHas('package_user', [
            'package_id' => $package->id,
            'user_id' => $user->id,
            'alias' => $data['alias']
        ]);
    }

    /** @test */
    public function it_should_ensure_users_can_not_edit_package_meta_from_other_user()
    {
        $package = Package::factory()->create();
        $andrew = User::factory()->create();
        $andrew->favorite($package);

        $robert = $this->signIn();
        $robert->favorite($package);

        $this->patchJson(route('api.v1.packages.update', $package->code), [
            'icon' => PackageIcon::random(),
            'alias' => $this->faker->word()
        ])->assertOk();

        $this->assertDatabaseHas('package_user',
            [
                'package_id' => $package->id,
                'user_id' => $andrew->id,
                'icon' => PackageIcon::DEFAULT->value,
                'alias' => $package->code
            ])->assertDatabaseCount('package_user', 2);
    }
}
