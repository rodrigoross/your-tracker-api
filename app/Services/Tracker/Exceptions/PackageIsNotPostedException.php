<?php

namespace App\Services\Tracker\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class PackageIsNotPostedException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render(): Response
    {
        return response()->json([
            'message' => __('Package not found or isn\'t posted'),
        ], Response::HTTP_NOT_FOUND);
    }
}
