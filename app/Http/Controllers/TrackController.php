<?php

namespace App\Http\Controllers;

use App\Services\Tracker\Contracts\TrackerServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Tracking",
    description: "Tracking API route"
)]
class TrackController extends Controller
{
    #[OA\Get(
        path: '/api/v1/track',
        operationId: 'v1.track',
        description: 'Route to track packages',
        summary: 'API middleware route to track and map services responses',
        tags: ['Tracking'],
        parameters: [
            new OA\Parameter(
                name: 'code',
                description: 'Code of the package to be tracked',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    description: 'Delivery package code',
                    type: 'string',
                    example: 'NL718729417BR',
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful tracking operation',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'trackSuccess',
                            summary: 'Successful tracking response',
                            value: [
                                "code" => "NL718729417BR",
                                "host" => "your-tracker.com",
                                "lastEventAt" => "2022-08-01T00:00:00.000000Z",
                                "events" => [
                                    [
                                        "date" => "08/01/2022 00:00",
                                        "location" => "São Paulo - SP",
                                        "status" => "POSTED",
                                        "message" => "Objeto postado",
                                        "subStatus" => [],
                                    ]
                                ]
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Code missing or malformed',
                content: new OA\JsonContent(
                    example: [
                        "message" => "No package code provided",
                    ]
                )
            )
        ]
    )]
    public function __invoke(Request $request, TrackerServiceInterface $service)
    {
        try {
            return response()->json($service->track($request->query('code')));
        } catch (\TypeError $th) {
            return response()->json([
                'message' => __('No package code provided'),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $th) {
            Log::driver('tracker')
                ->error($th->getMessage(), [
                    'code' => $request->query('code'),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                ]);

            return response()->json([
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
