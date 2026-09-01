<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Interfaces\Auth\AuthServiceInterface;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthServiceInterface $authService)
    {
        try {
            $data = $request->validated();
            $user = $authService->login($data);

            return ResponseHandler::success($user, 'Sesión Iniciada Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function register(RegisterRequest $request, AuthServiceInterface $authService)
    {
        try {
            $data = $request->validated();
            $user = $authService->register($data);

            $response = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ];

            return ResponseHandler::success($response, 'Usuario Creado Correctamente', 201);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function checkstatus()
    {
        try {
            $user = auth()->user();
            $token = JWTAuth::fromUser($user);

            $data = ['name' => $user->name, 'role' => $user->role, 'token' => $token];

            return ResponseHandler::success($data, 'Usuario Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }
}
