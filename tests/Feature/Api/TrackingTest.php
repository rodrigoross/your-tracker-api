<?php

namespace Tests\Feature\Api;

use App\Services\Tracker\Contracts\TrackerServiceInterface;
use App\Services\Tracker\Entities\Tracker;
use App\Services\Tracker\Exceptions\PackageIsNotPostedException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function guests_can_track_packages()
    {
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

        $this->getJson(route('api.v1.track', ['code' => 'NL718729417BR']))
            ->assertOk()
            ->assertJsonFragment([
                    'code' => 'NL718729417BR',
                    'host' => 'rd',
                    'lastEventAt' => '2022-01-01T19:32:00.000000Z',
                    'events' => [
                        [
                            'datetime' => '2022-01-01T00:00:00.000000Z',
                            'location' => 'São Paulo - SP',
                            'status' => 'POSTED',
                            'message' => 'Objeto postado',
                            'subStatus' => [],
                        ]
                    ]
                ]
            );
    }

    /** @test */
    public function it_should_ensure_a_code_is_provided()
    {
        $this->getJson(route('api.v1.track'))
            ->assertBadRequest()
            ->assertJsonFragment([
                'message' => __('No package code provided'),
            ]);
    }
}
