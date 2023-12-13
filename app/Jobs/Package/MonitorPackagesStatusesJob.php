<?php

namespace App\Jobs\Package;

use App\Enums\PackageStatus;
use App\Models\Package;
use App\Services\Tracker\Contracts\TrackerServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Throwable;

class MonitorPackagesStatusesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('default');
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $packages = Package::whereStatus(PackageStatus::ACTIVE->value)->get();

        if ($packages->isEmpty()) {
            return;
        }

        Bus::batch(
            $packages->map(fn(Package $package) => new UpdatePackageDataJob($package))->toArray()
        )->onQueue('packages')
        ->dispatch();
    }
}
