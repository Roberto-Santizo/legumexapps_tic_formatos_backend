<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(['admin', 'adminagricola', 'user'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El campo de nombre es obligatorio.',
            'username.required' => 'El campo de usuario es obligatorio.',
            'username.unique' => 'El usuario ya se encuentra registrado.',
            'password.required' => 'El campo de contraseña es obligatorio.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'role.required' => 'El campo de rol es obligatorio.',
            'role.in' => 'El rol seleccionado no es válido.',
        ];
    }
}
