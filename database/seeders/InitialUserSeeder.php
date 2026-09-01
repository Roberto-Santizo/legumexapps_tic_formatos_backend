<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crea el usuario administrador inicial para poder hacer login en un
     * despliegue recien levantado. Es idempotente: si el usuario ya existe no
     * se toca, para no pisar una contrasena que ya haya cambiado el equipo.
     */
    public function run(): void
    {
        $username = config('app.initial_admin.username');

        if (User::where('username', $username)->exists()) {
            $this->command?->info("Usuario inicial '{$username}' ya existe, no se modifica.");

            return;
        }

        $password = config('app.initial_admin.password');

        User::create([
            'name' => config('app.initial_admin.name'),
            'username' => $username,
            'role' => 'admin',
            'password' => Hash::make($password),
        ]);

        $this->command?->warn("Usuario inicial '{$username}' creado. Cambia su contrasena cuanto antes.");
    }
}
