<?php

namespace App\Http\Middleware;

use App\Errors\UnauthorizedError;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user->role != 'admin') {
            throw new UnauthorizedError('No autorizado');
        }

        return $next($request);
    }
}
