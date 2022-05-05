<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContrAgentRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $auth = Auth::guard('api');
        abort_unless($auth->check(), 401);
        $user = $auth->user();
        foreach ($roles as $role) {
            if ($user->role === $role && $user->hasContrAgent()) {
                $request->user = $user;
                return $next($request);
            }
        }
        return response([
            'error' => [
                'code' => 403,
                'message' => 'Вы не имеете разрешения на совершение данного действия. Возможно вам не назначили роль и (или) отстутствует привязка к вашей компании.'
            ]
        ], 403);
    }
}
