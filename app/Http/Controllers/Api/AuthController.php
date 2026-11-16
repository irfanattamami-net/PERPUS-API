<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Authentication',
    description: 'Registrasi, login, dan pengelolaan autentikasi pengguna'
)]
class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/register',
        summary: 'Registrasi pengguna baru',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Admin Perpustakaan'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Registrasi berhasil'),
            new OA\Response(response: 422, description: 'Validasi gagal')
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 201);
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Login pengguna',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                    new OA\Property(property: 'device_name', type: 'string', nullable: true, example: 'Laptop')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login berhasil'),
            new OA\Response(response: 422, description: 'Email atau password salah')
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken(
            $request->input('device_name') ?: 'api-token'
        )->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    #[OA\Get(
        path: '/api/me',
        summary: 'Menampilkan profil pengguna yang sedang login',
        tags: ['Authentication'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Profil pengguna berhasil ditampilkan'),
            new OA\Response(response: 401, description: 'Tidak terautentikasi')
        ]
    )]
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Logout pengguna',
        tags: ['Authentication'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logout berhasil'),
            new OA\Response(response: 401, description: 'Tidak terautentikasi')
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
