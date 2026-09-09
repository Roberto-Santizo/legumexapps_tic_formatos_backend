<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::orderBy('name')->get();
            $data = UserResource::collection($users);

            return ResponseHandler::success($data, 'Usuarios Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * La contraseña se hashea en el modelo (cast `hashed`).
     */
    public function store(CreateUserRequest $request)
    {
        try {
            $user = User::create($request->validated());

            return ResponseHandler::success(new UserResource($user), 'Usuario Creado Correctamente', 201);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = $this->findUserOrFail($id);

            return ResponseHandler::success(new UserResource($user), 'Usuario Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * Si no se envía contraseña, se conserva la que el usuario ya tenía.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $user = $this->findUserOrFail($id);

            $data = $request->validated();

            if (blank($data['password'] ?? null)) {
                unset($data['password']);
            }

            $user->update($data);

            return ResponseHandler::success(new UserResource($user), 'Usuario Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el usuario no existe.
     */
    private function findUserOrFail(string $id): User
    {
        $user = User::find($id);

        if (! $user) {
            throw new NotFoundError('Usuario no encontrado');
        }

        return $user;
    }
}
