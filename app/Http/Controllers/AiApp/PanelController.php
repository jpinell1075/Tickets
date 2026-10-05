<?php

namespace App\Http\Controllers\AiApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PanelController extends Controller
{
    /**
     * Display the AI assistant panel view.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('ai_app.panel');
    }
}