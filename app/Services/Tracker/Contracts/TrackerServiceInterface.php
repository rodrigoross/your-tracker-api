<?php

namespace App\Services\Tracker\Contracts;

use App\Services\Tracker\Entities\Tracker;

interface TrackerServiceInterface
{
    public function track(string $code): Tracker;
}
