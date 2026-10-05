<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    /**
     * Display the main panel view.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $userApps = Auth::user()->userApps();

        if($userApps->count() === 1 && $userApps->first()->access_route_name) {
            return redirect()->route($userApps->first()->access_route_name);
        }

        return Inertia::render('panel', [
            'userApps' => $userApps
        ]);
    }
}
