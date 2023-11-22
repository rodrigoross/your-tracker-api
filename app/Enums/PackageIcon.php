<?php

namespace App\Enums;

use Illuminate\Support\Facades\Log;

enum PackageIcon: string
{
    case DEFAULT = 'default';
    case CLOTHING = 'clothing';
    case TECHNOLOGY = 'technology';
    case GIFT = 'gift';
    case MISCELLANEOUS = 'miscellaneous';
    case APPLIANCES = 'appliances';

    public static function fromName(string $name): string
    {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status->value;
            }
        }

        Log::warning("{$name} is not a valid name for enum " . self::class);

        return self::DEFAULT->value;
    }

    public static function fromValue(string $value): string
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return $status->name;
            }
        }

        Log::warning("$value is not a valid value for enum " . self::class);

        return self::DEFAULT->name;
    }

    public static function toArray(): array
    {
        return collect(self::cases())->map(fn ($status) => $status->value)->toArray();
    }

    // Test Helpers
    public static function random(): string
    {
        return collect(self::cases())->except(self::DEFAULT->value)->random()->value;
    }
}
