<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Register Success');
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Login Success');
    }

    public function profile(Request $request)
    {
        return $this->successResponse([
            'user' => new UserResource($request->user()),
        ], 'Profile fetched successfully');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout Success');
    }

    public function me(Request $request)
    {
        return $this->successResponse([
            'user' => new UserResource($request->user()),
        ], 'User fetched successfully');
    }

    public function roles()
    {
        return $this->successResponse([
            'roles' => [
                ['id' => 1, 'name' => 'admin'],
                ['id' => 2, 'name' => 'teacher'],
                ['id' => 3, 'name' => 'student'],
            ],
        ], 'Roles fetched successfully');
    }
}
