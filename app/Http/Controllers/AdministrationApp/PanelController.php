<?php

namespace App\Http\Controllers\AdministrationApp;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class PanelController extends Controller
{
    /**
     * Display the administration panel view.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $app_internal_name = 'administration_app';
        $userModules = auth()->user()->modules();
        $userModules = $userModules->filter(function ($module) use ($app_internal_name) {
            return $module->app->internal_name === $app_internal_name;
        })->values();
        
        if ($userModules->count() === 1) {
            return redirect()->route($userModules->first()->access_route_name);
        }

        return Inertia::render('administration_app.panel');
    }
}
