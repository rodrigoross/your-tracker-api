<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '0.0.1',
    description: 'Free project that consumes the Correios API for the your-tracker mobile app.',
    title: 'Your Tracker API',
    contact: new OA\Contact(name: 'Rodrigo de Sousa', email: 'rodrigo.ross.comp@gmail.com'),
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
