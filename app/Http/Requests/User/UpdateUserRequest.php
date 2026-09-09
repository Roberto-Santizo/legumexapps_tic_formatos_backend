<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * La contraseña es opcional: si no se envía, el usuario conserva la que tenía.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($this->route('id'))],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'adminagricola', 'user'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El campo de nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'username.required' => 'El campo de usuario es obligatorio.',
            'username.max' => 'El usuario no puede tener más de 255 caracteres.',
            'username.unique' => 'El usuario ya se encuentra registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'role.required' => 'El campo de rol es obligatorio.',
            'role.in' => 'El rol seleccionado no es válido.',
        ];
    }
}
