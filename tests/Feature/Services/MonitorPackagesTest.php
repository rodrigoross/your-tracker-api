<?php

namespace Tests\Feature\Services;

use App\Enums\PackageStatus;
use App\Jobs\Package\MonitorPackagesStatusesJob;
use App\Jobs\Package\UpdatePackageDataJob;
use App\Models\Event;
use App\Models\Package;
use App\Models\User;
use App\Notifications\PackageUpdatedNotification;
use App\Services\Tracker\Contracts\TrackerServiceInterface;
use App\Services\Tracker\Entities\Tracker;
use App\Services\Tracker\Enums\TrackingStatus;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Tests\TestCase;

class MonitorPackagesTest extends TestCase
{
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(TrackerServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('track')
                ->withArgs(['NL718729417BR'])
                ->andReturn(new Tracker(
                    'NL718729417BR',
                    'rd',
                    '2022-01-02T19:32:00.000000Z',
                    events: [
                        [
                            'data' => '02/01/2022',
                            'hora' => '16:00',
                            'local' => 'Cuiabá - MT',
                            'status' => 'Objeto entregue ao destinatário',
                            'subStatus' => [],
                        ],
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
    public function it_should_batch_active_packages_when_monitoring_status()
    {
        Bus::fake();

        Package::factory()->count(5)->create();

        $job = new MonitorPackagesStatusesJob();
        $job->handle();

        Bus::assertBatched(function (PendingBatch $batch) {
            return $batch->jobs->count() === 5;
        });
    }

    /** @test */
    public function it_should_not_batch_completed_packages_when_monitoring_status()
    {
        Bus::fake();

        Package::factory()->count(5)->completed()->create();

        $job = new MonitorPackagesStatusesJob();
        $job->handle();

        Bus::assertNothingBatched();
    }

    /** @test */
    public function monitoring_job_should_complete_delivered_packages()
    {
        $this->withoutExceptionHandling();
        $package = Package::factory()
            ->has(
                Event::factory()
                    ->state([
                        'datetime' => '01/01/2022 00:00:00',
                        'location' => 'São Paulo - SP',
                        'status' => TrackingStatus::POSTED->value,
                        'message' => 'Objeto postado',
                        'subStatus' => [],
                    ])
            )
            ->state([
                'code' => 'NL718729417BR',
                'last_event_at' => '2022-01-01T20:00:00.000000Z',
            ])->create();

        UpdatePackageDataJob::dispatch($package);

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => PackageStatus::COMPLETED->value
        ]);

        $this->assertDatabaseCount('events', 2);
    }

    /** @test */
    public function it_should_notify_users_of_package_updates()
    {
        Notification::fake();

        $user = User::factory()->create();
        $package = Package::factory()
            ->has(
                Event::factory()
                    ->state([
                        'datetime' => '01/01/2022 00:00:00',
                        'location' => 'São Paulo - SP',
                        'status' => TrackingStatus::POSTED->value,
                        'message' => 'Objeto postado',
                        'subStatus' => [],
                    ])
            )
            ->state([
                'code' => 'NL718729417BR',
                'last_event_at' => '2022-01-01T20:00:00.000000Z',
            ])->create();

        $user->favorite($package);

        UpdatePackageDataJob::dispatch($package);

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => PackageStatus::COMPLETED->value
        ]);

        $this->assertDatabaseCount('events', 2);

        Notification::assertSentTo([$user], PackageUpdatedNotification::class);
    }
}
