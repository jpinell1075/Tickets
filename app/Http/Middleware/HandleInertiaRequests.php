<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = Auth::user();

        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'userName' => $user ? $user->name : null,
            'modules' => $user ? $user->modules()
            ->where('show_in_menu', true)
            ->map(function ($module) {
                return [
                    'name' => $module->name,
                    'icon' => $module->icon,
                    'route' => Route::has($module->access_route_name) ? route($module->access_route_name, [], false) : null,
                    'order' => $module->order,
                    'app' => $module->app->internal_name,
                ];
            }) : [],
            'flash' => [
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ]);
    }
}
