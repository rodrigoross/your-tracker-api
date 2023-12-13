<?php

namespace App\Services\Tracker\Entities;

use Carbon\Carbon;

class Event
{
    public function __construct(
        public Carbon $datetime,
        public string $location,
        public string $status,
        public string $message,
        public array  $subStatus,
    )
    {
    }
}
