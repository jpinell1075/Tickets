<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseFormRequest;

/**
 * Handles exporting and printing functionality.
 */
class PrintController extends Controller
{
    /**
     * Render a view for printing purposes.
     *
     * @param \App\Http\Requests\BaseFormRequest $request
     * @return \Illuminate\View\View
     */
    public function print(BaseFormRequest $request)
    {
        $request->validate([
            'view_name' => 'required|string',
        ], [
            'view_name.required' => 'El nombre de la vista es requerido',
            'view_name.string' => 'El nombre de la vista debe ser una cadena',
        ]);

        $view_name = $request->input('view_name');
        $title = $request->input('title', null);
        $params = json_decode($request->input('params', null), true);
        $pageProperties = json_decode($request->input('page_properties', null), true);

        return view('exports.print', compact('view_name', 'params', 'title', 'pageProperties'));
    }
}
