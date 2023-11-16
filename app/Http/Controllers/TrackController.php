<?php

namespace App\Http\Controllers;

use App\Services\Tracker\Contracts\TrackerServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackController extends Controller
{
    public function __invoke(Request $request, TrackerServiceInterface $service)
    {
        try {
            return response()->json([
                $service->track($request->query('code'))
            ]);
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
