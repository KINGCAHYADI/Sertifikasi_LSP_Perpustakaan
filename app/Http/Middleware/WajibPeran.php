<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WajibPeran
{
    public function handle(Request $request, Closure $next, string $peran)
    {
        $user = session('user');
        if (!$user || ($user['peran'] ?? null) !== $peran) {
            abort(403, 'Akses ditolak.');
        }
        return $next($request);
    }
}
