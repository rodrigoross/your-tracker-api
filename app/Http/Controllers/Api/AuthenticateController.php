<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag(
    name: "Authentication",
    description: "Authentication routes"
)]
class AuthenticateController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        operationId: 'api.login',
        description: 'Authentication route to login a user',
        summary: 'Route to authenticate users',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string'),
                    new OA\Property(property: 'device_name', type: 'string'),]
            )
        ),
        tags: ['Authentication'],
        responses: [new OA\Response(
            response: 200,
            description: 'Login successful',
            content: new OA\JsonContent(
                examples: [new OA\Examples(
                    example: 'plainAccessToken',
                    summary: 'Successful Login response',
                    value: [
                        "user" => [
                            "name" => "Rodrigo",
                            "email" => "p8jH6@example.com",
                            "email_verified_at" => null,
                        ],
                        "plainTextToken" => "1|5oOt1FajQlwabQ4NRmODi3hxPgATzR4tsL9AQtSf47929443",
                    ]
                )],
            ),
        ),
            new OA\Response(
                response: 422,
                description: 'Validation errors',
                content: new OA\JsonContent(
                    examples: [new OA\Examples(
                        example: 'loginError',
                        summary: 'Validation errors response',
                        value: ['message' => 'The given data was invalid.',
                            'errors' => ['email' => ['The email field is required.'],
                                'password' => ['The password field is required.'],
                                'device_name' => ['The device name field is required.'],]]
                    )]
                )
            ),]
    )]
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'user' => new UserResource($user),
            'plainTextToken' => $user->createToken($validated['device_name'])->plainTextToken
        ]);
    }

    #[OA\Post(
        path: '/api/register',
        operationId: 'api.register',
        description: 'Authentication route to register new users',
        summary: 'Register new users',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                    new OA\Property(property: 'device_name', type: 'string'),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Registration successful',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'registerSuccess',
                            summary: 'Successful registration response',
                            value: [
                                "user" => [
                                    "name" => "Rodrigo",
                                    "email" => "p8jH6@example.com",
                                    "email_verified_at" => null,
                                ],
                                "plainTextToken" => "1|5oOt1FajQlwabQ4NRmODi3hxPgATzR4tsL9AQtSf47929443",
                            ]
                        )]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation errors',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'registerError',
                            summary: 'Validation errors response',
                            value: [
                                'message' => 'The given data was invalid.',
                                'errors' => ['name' => ['The name field is required.'],
                                    'email' => ['The email field is required.'],
                                    'password' => ['The password field is required.'],
                                    'password_confirmation' => ['The password confirmation field is required.'],
                                    'device_name' => ['The device name field is required.'],
                                ]
                            ]
                        )
                    ]
                )
            )]
    )]
    public function register(StoreUserRequest $request)
    {
        $user = User::create(
            array_merge(
                $request->safe(['name', 'email']),
                ['password' => Hash::make($request->input('password'))]
            ),
        );

        return response()->json([
            'user' => new UserResource($user),
            'plainTextToken' => $user->createToken($request->input('device_name'))->plainTextToken
        ], Response::HTTP_CREATED);
    }

    #[OA\Delete(
        path: '/api/logout',
        operationId: 'api.logout',
        description: 'Authentication route to logout users',
        summary: 'Invalidate user tokens and logout',
        security: ['sanctum'],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Successful operation',
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'logoutError',
                            summary: 'Unauthenticated response',
                            value: ['message' => 'Unauthenticated.']
                        )
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([], 204);
    }
}
