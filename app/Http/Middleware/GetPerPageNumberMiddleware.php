<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GetPerPageNumberMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $per_page = isset($request->per_page) && (int)$request->per_page ? abs($request->per_page) : config('pagination.per_page');
        $request->per_page = $per_page;
        return $next($request);
    }
}
