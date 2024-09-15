<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends Controller
{
    #[Route('/api/auth/login', methods: ['POST'], name: 'login_user')]
    #[OA\Post(
        path: '/api/auth/login',
        summary: 'User login',
        tags: ['Auth'],
        parameters: [
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Login credentials',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'token',
                            type: 'string',
                            example: 'JWT_TOKEN_HERE'
                        ),
                        new OA\Property(
                            property: 'token_type',
                            type: 'string',
                            example: 'Bearer'
                        ),
                        new OA\Property(
                            property: 'expires_at',
                            type: 'string',
                            example: '0000-00-00 00:00:00'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials'
            )
        ]
    )]
    public function login(LoginRequest $request)
    {
        $request->authenticate();

        $tokenName = 'Login Token -' . $request->ip();

        $expiration = now()->addWeek();

        $tokenResult = $request->user()->createToken($tokenName, ['*'], $expiration);

        return [
            'token' => $tokenResult->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiration->toDateTimeString(),
        ];
    }

    #[Route('/api/auth/logout', methods: ['POST'], name: 'logout_user')]
    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'User logout',
        tags: ['Auth'],
        parameters: [
            new OA\Parameter(
                name: 'Authorization',
                in: 'header',
                required: true,
                description: 'Bearer token for user authentication',
                schema: new OA\Schema(type: 'string', example: 'Bearer JWT_TOKEN_HERE')
            ),
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Media type expected by the client',
                schema: new OA\Schema(type: 'string', example: 'application/json')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Logout successful'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Invalid or missing token'
            )
        ]
    )]
    public function logout(Request $request)
    {
        //Auth::guard('web')->logout();

        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
