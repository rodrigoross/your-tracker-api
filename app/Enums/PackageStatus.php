<?php

namespace App\Enums;

use App\Traits\Enum\EnumHelpers;

enum PackageStatus: string
{
    use EnumHelpers;

    case ACTIVE = 'active';
    case COMPLETED = 'completed';
}
