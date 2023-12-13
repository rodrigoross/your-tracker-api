<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    private array $statuses = [
        'POSTED',
        'INSPECTION',
        'PENDING',
        'PAYED',
        'RECEIVED',
        'IN_TRANSIT',
        'DELIVERING',
        'DELIVERED',
        'UNDEFINED',
    ];

    public function definition(): array
    {
        return [
            'datetime' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            'location' => $this->faker->city(),
            'status' => collect($this->statuses)->random(),
            'message' => $this->faker->sentence(3),
            'subStatus' => [],
            'package_id' => Package::factory(),
        ];
    }
}
