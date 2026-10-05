<?php

namespace App\Http\Controllers\InventoryApp\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\Inventario\Main;
use Maatwebsite\Excel\Facades\Excel;

class ReportesInventarioController extends Controller
{
    public function index()
    {
        return Inertia::render('inventory_app.inventory_reports.index');
    }

    public function generateGeneralReport()
    {
        Return Excel::download(new Main(), 'Reporte_General_de_Inventario.xlsx');
    }
}
