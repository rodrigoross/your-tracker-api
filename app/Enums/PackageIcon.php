<?php

namespace App\Enums;

use App\Traits\Enum\EnumHelpers;

enum PackageIcon: string
{
    use EnumHelpers;

    case DEFAULT = 'default';
    case CLOTHING = 'clothing';
    case TECHNOLOGY = 'technology';
    case GIFT = 'gift';
    case MISCELLANEOUS = 'miscellaneous';
    case APPLIANCES = 'appliances';

    // Test Helpers
    public static function random(): string
    {
        return collect(self::cases())->except(self::DEFAULT->value)->random()->value;
    }
}
