<?php

namespace Database\Factories;

use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->regexify('[A-Z]{2}[0-9]{9}[A-Z]{2}'),
            'last_event_at' => $this->faker->dateTime(),
            'status' => PackageStatus::ACTIVE->value,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'deleted_at' => null,
        ];
    }

    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PackageStatus::COMPLETED->value,
                'last_event_at' => Carbon::now()->subDay(),
            ];
        });
    }
}
