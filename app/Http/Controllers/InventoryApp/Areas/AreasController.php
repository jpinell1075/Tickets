<?php

namespace App\Http\Controllers\InventoryApp\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Areas\Area;
use App\Models\Areas\HistorialEncargadoArea;
use Illuminate\Support\Facades\DB;

class AreasController extends Controller
{
    public function index()
    {
        return Inertia::render('inventory_app.areas.index', [
            'model' => Area::getStaticData(),
        ]);
    }

    public function show(Area $area)
    {
        $historialEncargados = HistorialEncargadoArea::where('id_area', $area->id)
            ->with('encargado')
            ->orderBy('fecha_asignacion', 'desc')
            ->simplePaginate();
        return Inertia::render('inventory_app.areas.show', [
            'area' => $area->load('encargado'),
            'historialEncargados' => $historialEncargados,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activo,inactivo',
            'id_usuario_encargado' => 'nullable|exists:users,id',
        ]);

        try{
            DB::beginTransaction();

            $area = new Area();
            $area->nombre = $request->nombre;
            $area->descripcion = $request->descripcion;
            $area->estado = $request->estado;
            $area->id_usuario_encargado = $request->id_usuario_encargado;
            $area->save();

            if($area->id_usuario_encargado){
                $historial = new HistorialEncargadoArea();
                $historial->id_area = $area->id;
                $historial->id_usuario_encargado = $request->id_usuario_encargado;
                $historial->fecha_asignacion = now();
                $historial->save();
            }

            DB::commit();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Área creada exitosamente',
                    'type' => 'success',
                ],
            ]);
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al crear el área',
                    'type' => 'error',
                ],
            ]);
        }

    }

    public function update(Request $request, Area $area)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activo,inactivo',
            'id_usuario_encargado' => 'nullable|exists:users,id',
        ]);

        try{
            DB::beginTransaction();

            if($area->id_usuario_encargado && $request->id_usuario_encargado && $request->id_usuario_encargado != $area->id_usuario_encargado){
                HistorialEncargadoArea::where('id_area', $area->id)
                    ->where('id_usuario_encargado', $area->id_usuario_encargado)
                    ->whereNull('fecha_fin')
                    ->update(['fecha_fin' => now()]);

                $newhistorial = new HistorialEncargadoArea();
                $newhistorial->id_area = $area->id;
                $newhistorial->id_usuario_encargado = $request->id_usuario_encargado;
                $newhistorial->fecha_asignacion = now();
                $newhistorial->save();
            }

            $area->nombre = $request->nombre;
            $area->descripcion = $request->descripcion;
            $area->estado = $request->estado;
            $area->id_usuario_encargado = $request->id_usuario_encargado;
            $area->save();

            DB::commit();
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Área actualizada exitosamente',
                    'type' => 'success',
                ],
            ]);
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al actualizar el área',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function delete(Area $area)
    {
        if($area->delete()){
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Área eliminada exitosamente',
                    'type' => 'success',
                ],
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Error al eliminar el área',
                'type' => 'error',
            ],
        ]);
    }
}
