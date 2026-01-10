<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WajibLogin
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('user')) {
            return redirect('/masuk')->with('gagal', 'Silakan login dulu.');
        }
        return $next($request);
    }
}
