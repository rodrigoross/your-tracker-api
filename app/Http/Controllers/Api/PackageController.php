<?php

namespace App\Http\Controllers\Api;

use App\Enums\PackageIcon;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Http\Resources\PackageCollection;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use App\Services\Tracker\Contracts\TrackerServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Packages',
    description: 'Manage user saved packages',
)]
class PackageController extends Controller
{
    #[OA\Get(
        path: '/api/v1/packages',
        operationId: 'v1.packages.index',
        description: 'Route to list user saved packages',
        summary: 'API middleware route to list user saved packages',
        security: ['sanctum'],
        tags: ['Packages'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful listing of packages',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/PackageCollection'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            )
        ]
    )]
    public function index()
    {
        $this->authorize('viewAny', Package::class);

        return new PackageCollection(
            Auth::user()->packages()->with('events')->latest()->paginate(5)
        );
    }

    #[OA\Post(
        path: '/api/v1/packages',
        operationId: 'v1.packages.store',
        description: 'Route to favorite a package',
        summary: 'API middleware route to favorite a package',
        security: ['sanctum'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'code', type: 'string', example: 'NL718729417BR')]
            )
        ),
        tags: ['Packages'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Package favorited successfully',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/PackageResource',
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Package not found or haven\'t been posted',
            ),
            new OA\Response(
                response: 422,
                description: 'Package code invalid or not provided',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'validationError',
                            summary: 'Validation store error',
                            description: 'Validation error',
                            value: [
                                "message" => "The given data was invalid.",
                                "errors" => [
                                    "code" => [
                                        "The code is required.",
                                    ]
                                ]
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 503,
                description: 'Service unavailable',
            )
        ]
    )]
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

    #[OA\Patch(
        path: '/api/v1/packages/{package}',
        operationId: 'v1.packages.update',
        description: 'Route to update a package meta information',
        summary: 'API middleware route to update a package meta information',
        security: ['sanctum'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'icon', type: 'string', enum: [
                        'default',
                        'clothing',
                        'technology',
                        'gift',
                        'miscellaneous',
                        'appliances',
                    ], example: 'clothing'),
                    new OA\Property(property: 'alias', type: 'string', example: 'My favorited package'),
                ]
            )
        ),
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(
                name: 'package',
                description: 'Tracking package code',
                in: 'path',
                required: true,
                schema: new OA\Schema(description: 'Tracking package code', type: 'string', example: 'NL718729417BR'),
                example: 'NL718729417BR'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Package updated',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/PackageResource',
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    example: [
                        "message" => "You do not have access to this package, favorite it first",
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    example: [
                        "message" => "The given data was invalid.",
                        "errors" => [
                            "icon" => [
                                "The icon must be one of:" .
                                " default, clothing, technology, gift, miscellaneous, appliances.",
                            ],
                            "alias" => [
                                "The alias must be a string.",
                            ]
                        ]
                    ]
                )
            )
        ]
    )]
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

    #[OA\Get(
        path: '/api/v1/packages/{package}',
        operationId: 'v1.packages.show',
        description: 'Route to show a package',
        summary: 'API middleware route to show a package',
        security: ['sanctum'],
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(
                name: 'package',
                description: 'Package code',
                in: 'path',
                required: true,
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Package',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/PackageResource',
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
            )
        ]
    )]
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

    #[OA\Delete(
        path: '/api/v1/packages/{package}',
        operationId: 'v1.packages.destroy',
        description: 'Route to unfavorite a package',
        summary: 'API middleware route to unfavorite a package',
        security: ['sanctum'],
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(
                name: 'package',
                description: 'Package code',
                in: 'path',
                required: true,
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Package unfavored successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Package not found',
            ),
            new OA\Response(
                response: 412,
                description: 'Package not favorited',
                content: new OA\JsonContent(
                    example: [
                        "message" => "We could not find this package, have you favorited it?",
                    ]
                )
            )
        ]
    )]
    public function destroy(Package $package)
    {
        try {
            $this->authorize('delete', $package);

            Auth::user()->unfavorite($package);

            return response()->json([], 204);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'message' => __('We could not find this package, have you favorited it?'),
            ], Response::HTTP_PRECONDITION_FAILED);
        }
    }
}
