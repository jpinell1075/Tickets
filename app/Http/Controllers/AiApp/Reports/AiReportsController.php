<?php

namespace App\Http\Controllers\AiApp\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiReportsController extends Controller
{
    public function querys()
    {
        return Inertia::render('ai_app.reports.querys');
    }
}
