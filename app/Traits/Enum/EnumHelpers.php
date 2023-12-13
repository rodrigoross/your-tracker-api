<?php

namespace App\Traits\Enum;

use Illuminate\Support\Facades\Log;
use Psy\Exception\TypeErrorException;

trait EnumHelpers
{
    /**
     * @throws TypeErrorException
     */
    public static function fromName(string $name): string
    {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status->value;
            }
        }

        Log::warning("{$name} is not a valid name for enum " . self::class);

        throw new TypeErrorException("{$name} is not a valid name for enum " . self::class);
    }


    /**
     * @throws TypeErrorException
     */
    public static function fromValue(string $value): string
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return $status->name;
            }
        }

        Log::warning("$value is not a valid value for enum " . self::class);

        throw new TypeErrorException("{$value} is not a valid value for enum " . self::class);
    }

    public static function toArray(): array
    {
        return collect(self::cases())->map(fn ($status) => $status->value)->toArray();
    }
}
