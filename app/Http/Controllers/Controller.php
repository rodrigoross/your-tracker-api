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
#[OA\Schema(
    schema: 'PaginationMeta',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 2),
        new OA\Property(property: 'from', type: 'integer', example: 1),
        new OA\Property(property: 'last_page', type: 'integer', example: 10),
        new OA\Property(property: 'total', type: 'integer', example: 100),
        new OA\Property(property: 'to', type: 'integer', example: 5),
        new OA\Property(property: 'per_page', type: 'integer', example: 10),
        new OA\Property(property: 'path', type: 'string', example: 'https://app.com.br/api/model'),
        new OA\Property(
            property: 'links',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/MetaPaginationLinks')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PaginationLink',
    type: 'array',
    items: new OA\Items(ref: '#/components/schemas/MetaPaginationLinks'),
)]
#[OA\Schema(
    schema: 'MetaPaginationLinks',
    properties: [
        new OA\Property(property: 'url', type: 'string', example: 'https://app.com.br/api/model?page=1'),
        new OA\Property(property: 'label', type: 'string', example: '1'),
        new OA\Property(property: 'active', type: 'boolean', example: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    ref: '#/components/schemas/Pagination',
    schema: 'PaginationLinks',
    type: 'object',
)]
#[OA\Schema(
    schema: 'Pagination',
    properties: [
        new OA\Property(property: 'first', type: 'string', example: 'https://app.com.br/api/model?page=1'),
        new OA\Property(property: 'last', type: 'string', example: 'https://app.com.br/api/model?page=10'),
        new OA\Property(property: 'prev', type: 'string', example: 'https://app.com.br/api/model?page=1'),
        new OA\Property(property: 'next', type: 'string', example: 'https://app.com.br/api/model?page=3'),
    ]
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
