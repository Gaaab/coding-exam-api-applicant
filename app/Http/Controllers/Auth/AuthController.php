<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Cashbee Coding Exam Api Documentation",
    version: "0.1"
)]
class AuthController extends Controller
{
    #[OA\Get(path: '/api/auth/login')]
    #[OA\Response(response: '200', description: 'Response array')]
    public function login(LoginRequest $request)
    {
        $request->authenticate();

        $tokenName = 'Personal Access Login Token -' . $request->ip();

        $expiration = now()->addWeek();

        $tokenResult = $request->user()->createToken($tokenName, ['*'], $expiration);

        return [
            'token' => $tokenResult->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiration->toDateTimeString(),
        ];
    }

    #[OA\Post(path: '/api/auth/logout')]
    #[OA\Response(response: '204', description: 'No Content')]
    public function logout(Request $request)
    {
        //Auth::guard('web')->logout();

        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
