<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRoles
{
    /**
     * Handle an incoming request and check if the user has the required role for a module.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure $next The next middleware in the pipeline.
     * @param string $module The module to check permissions for.
     * @return Response The HTTP response or a redirect if the user lacks permissions.
     */
    public function handle(Request $request, Closure $next, $module): Response
    {
        if(Auth::user()->isAdmin()) return $next($request);

        if(Auth::user()->hasAccessToModule($module, $request->method())) return $next($request);

        return redirect()->back()->with([
            'message' => [
                'message' => 'No tienes permisos para realizar esta acción.',
                'type' => 'danger',
            ],
        ], 403);
    }
}
