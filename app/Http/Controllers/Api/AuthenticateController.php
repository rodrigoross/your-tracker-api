<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;


class AuthenticateController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        operationId: 'api.login',
        description: 'Authentication route to login a user',
        summary: 'Login',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string'),
                    new OA\Property(property: 'device_name', type: 'string'),]
            )
        ),
        tags: ['Auth'],
        responses: [new OA\Response(
            response: 200,
            description: 'Successful operation',
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

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([], 204);
    }
}
