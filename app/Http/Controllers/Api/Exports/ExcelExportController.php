<?php

namespace App\Http\Controllers\Api\Exports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\GeneralExcelExport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Handles exporting data to Excel files.
 */
class ExcelExportController extends Controller
{
    /**
     * Export data to an Excel file and download it.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function excel(Request $request)
    {
        return Excel::download(new GeneralExcelExport($request->table_html, $request->file_name), "{$request->file_name}.xlsx");
    }
}
