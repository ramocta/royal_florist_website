<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;


class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->register($request->validated());

            return $this->successResponse([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ], 'Registrasi Berhasil', 200);
        } catch (Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());

            return $this->successResponse([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ], 'Login Berhasil', 200);
        } catch (Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return $this->successResponse(null, 'Logout Berhasil', 200);
    }
}
