<?php

namespace App\Interfaces\Auth;

use App\Models\User;

interface AuthServiceInterface
{
    public function login(array $data);

    public function register(array $data): User;
}
