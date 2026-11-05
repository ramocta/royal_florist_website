<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api\AuthServiceApi;
use App\Http\Requests\LoginRequest;
use App\Traits\ApiResponse;
use App\Http\Resources\UserResource;
use Illuminate\Validation\ValidationException;

class AuthControllerApi extends Controller
{
    use ApiResponse;

    public function __construct(protected AuthServiceApi $authService) {}

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->loginApi($request->validated());
            
            return $this->successResponse([
                'user' => new UserResource($result['user']),
                'token' => $result ['token'],
            ], 'Login Berhasil', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout Berhasil');
    }
}
