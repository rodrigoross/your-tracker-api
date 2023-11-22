<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Http\Resources\PackageCollection;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use App\Services\Tracker\Contracts\TrackerServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PackageController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Package::class);

        return new PackageCollection(
            Auth::user()->packages()->with('events')->latest()->paginate(5)
        );
    }

    public function store(StorePackageRequest $request, TrackerServiceInterface $trackerService)
    {
        $this->authorize('create', Package::class);

        $data = $trackerService->track($request->input('code'));

        return DB::transaction(function () use ($data) {
            $newPackage = Package::create([
                'code' => $data->code,
                'updated_at' => $data->lastEventAt,
            ]);

            $newPackage->events()->createMany(
                array_map(fn($event) => [
                    'datetime' => $event->datetime,
                    'status' => $event->status,
                    'message' => $event->message,
                    'sub_status' => $event->subStatus,
                    'location' => $event->location,
                ], $data->events)
            );

            Auth::user()->favorite($newPackage);

            $favoritedPackage = Auth::user()->packages()->where('code', $newPackage->code)->first();

            return (new PackageResource($favoritedPackage))->response()->setStatusCode(Response::HTTP_CREATED);
        });
    }

    public function update(UpdatePackageRequest $request, Package $package)
    {
        Auth::user()
            ->packages()
            ->updateExistingPivot(
                $package->id,
                $request->validated()
            );

        return new PackageResource(Auth::user()->packages()->where('code', $package->code)->first());
    }

    public function show(Package $package)
    {
        try {
            $this->authorize('view', $package);

            return new PackageResource(
                Auth::user()->packages()->where('code', $package->code)->first()
            );
        } catch (\Exception $exception) {
            return response()->json([
                'message' => __('You do not have access to this package, favorite it first'),
            ], Response::HTTP_FORBIDDEN);
        }
    }

    public function destroy(Package $package)
    {
        $this->authorize('delete', $package);

        Auth::user()->unfavorite($package);

        return response()->json([], 204);
    }
}
