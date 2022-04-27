<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{

    public function handle($request, Closure $next, ...$roles)
    {
//        dd($role);
//        dd(Auth::hasRole(env('KEYCLOAK_ALLOWED_RESOURCES'), $role));
        foreach ($roles as $role) {
            if (auth('webapi')->user()->hasRole($role)) {
                return $next($request);
            }
        }
        return response([
            'error' => [
                'code' => 403,
                'message' => 'Отказано в доступе'
            ]
        ], 403);

//        if ($permission !== null && !auth()->user()->can($permission)) {
//            abort(404);
//        }
    }
}
