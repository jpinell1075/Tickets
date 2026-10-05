<?php

namespace App\Http\Controllers\TicketsApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PanelController extends Controller
{
    /**
     * Display the tickets panel view.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $app_internal_name = 'tickets_app';
        $userModules = auth()->user()->modules();
        $userModules = $userModules->filter(function ($module) use ($app_internal_name) {
            return $module->app->internal_name === $app_internal_name;
        })->values();
        
        if ($userModules->count() === 1) {
            return redirect()->route($userModules->first()->access_route_name);
        }


        return Inertia::render('tickets_app.panel');
    }
}
