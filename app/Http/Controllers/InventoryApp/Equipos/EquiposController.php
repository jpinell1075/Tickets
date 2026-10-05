<?php

namespace App\Http\Controllers\InventoryApp\Equipos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Equipos\Equipo;
use App\Models\Equipos\CaracteristicaEquipo;
use App\Models\Equipos\Componente;
use App\Models\Equipos\CaracteristicaComponente;
use Illuminate\Support\Facades\DB;
use App\Models\Equipos\EquipoComentario;
use App\Models\Equipos\EquipoArchivo;
use App\Models\Equipos\EquipoPeriferico;
use App\Models\Equipos\HistorialAreaEquipo;
use App\Models\Equipos\EquipoPrestamo;
use App\Models\Tickets\Mantenimiento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EquiposController extends Controller
{
    public function index()
    {
        return Inertia::render('inventory_app.equipos.index', [
            'model' => Equipo::getStaticData(),
        ]);
    }

    public function show(Equipo $equipo)
    {
        $historialAreas = HistorialAreaEquipo::where('id_equipo', $equipo->id)
            ->with(['area', 'creator'])
            ->orderBy('fecha_asignacion', 'desc')
            ->simplePaginate(15);

        return Inertia::render('inventory_app.equipos.show', [
            'equipo' => $equipo->load([
                'usuario',
                'area',
                'caracteristicas',
                'componentes.caracteristicas',
                'comentarios' => function ($query) {
                    $query->with('creator')->orderBy('created_at', 'desc');
                },
                'archivos' => function ($query) {
                    $query->with('creator')->orderBy('created_at', 'desc');
                },
                'perifericos' => function ($query) {
                    $query->with('creator')->orderBy('created_at', 'desc');
                },
                'mantenimientos' => function ($query) {
                    $query->with(['encargado', 'ticket'])->orderBy('fecha', 'desc');
                },
                'prestamoActivo.areaDestino',
                'prestamoActivo.responsable',
                'prestamos' => function ($query) {
                    $query->with(['areaOrigen', 'areaDestino', 'responsable', 'creator'])->orderBy('fecha_prestamo', 'desc');
                },
            ]),
            'historialAreas' => $historialAreas,
        ]);
    }

    public function store(Request $request)
    {
        $available_types = 'cpu,monitor,ups,switches,aps,telefonia_ip,impresoras,reloj_biometrico,data_show,pantalla_proyector,laptops';
        $request->validate([
            'estado_funcional' => 'required|in:funcional,no_funcional',
            'estado' => 'required|in:activo,inactivo,mantenimiento',
            'tipo' => 'required|in:' . $available_types,
        ],[
            'estado_funcional.required' => 'El estado funcional es obligatorio.',
            'estado_funcional.in' => 'El estado funcional debe ser "Funcional" o "No Funcional".',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser "Activo", "Inactivo" o "Mantenimiento".',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
        ]);

        if(
            Equipo::where('cod_inventario', $request->cod_inventario)
            ->orWhereHas('caracteristicas', function ($query) use ($request) {
                $query->where('nombre', 'num_serie')
                    ->where('valor', $request->num_serie);
            })
            ->exists()
        ){
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Ya existe un equipo con el mismo código de inventario o número de serie.',
                    'type' => 'warning',
                ],
            ]);
        }

        try{
            DB::beginTransaction();
            // atributos directos
            $equipo = new Equipo();
            $equipo->id_usuario = $request->id_usuario;
            $equipo->id_area = $request->id_area;
            $equipo->estado = $request->estado;
            $equipo->tipo = $request->tipo;
            $equipo->cod_inventario = $request->cod_inventario;
            $equipo->nombre = $request->nombre;
            $equipo->save();

            if ($equipo->id_area) {
                HistorialAreaEquipo::create([
                    'id_equipo' => $equipo->id,
                    'id_area' => $equipo->id_area,
                    'fecha_asignacion' => now(),
                    'observacion' => 'Asignación inicial de área',
                ]);
            }

            //caracteristicas
            $caracteristics = [
                'marca' => $request->marca,
                'modelo' => $request->modelo,
                'num_serie' => $request->num_serie,
                'estado_funcional' => $request->estado_funcional,
            ];
            foreach($caracteristics as $key => $value){
                CaracteristicaEquipo::create([
                    'id_equipo' => $equipo->id,
                    'nombre' => $key,
                    'valor' => $value,
                ]);
            }
            foreach($request->caracteristicas as $key => $value){
                CaracteristicaEquipo::create([
                    'id_equipo' => $equipo->id,
                    'nombre' => $key,
                    'valor' => $value,
                ]);
            }

            //componentes
            foreach($request->componentes as $key => $value){
                if(count($value) > 0){
                    $component = new Componente();
                    $component->id_equipo = $equipo->id;
                    $component->nombre = $key;
                    $component->save();
                    foreach($value as $key2 => $value2){
                        CaracteristicaComponente::create([
                            'id_componente' => $component->id,
                            'nombre' => $key2,
                            'valor' => $value2,
                        ]);
                    };
                }
            }

            DB::commit();
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Equipo creado exitosamente',
                    'type' => 'success',
                ],
            ]);
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al crear el equipo',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function update(Request $request, Equipo $equipo)
    {
        $available_types = 'cpu,monitor,ups,switches,aps,telefonia_ip,impresoras,reloj_biometrico,data_show,pantalla_proyector,laptops';
        $request->validate([
            'id_usuario' => 'nullable|exists:users,id',
            'id_area' => 'nullable|exists:areas,id',
            'nombre' => 'nullable|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'num_serie' => 'nullable|string|max:255',
            'cod_inventario' => 'nullable|string|max:255',
            'estado_funcional' => 'required|in:funcional,no_funcional',
            'estado' => 'required|in:activo,inactivo,mantenimiento',
            'tipo' => 'required|in:' . $available_types,
        ],[
            'estado_funcional.required' => 'El estado funcional es obligatorio.',
            'estado_funcional.in' => 'El estado funcional debe ser "Funcional" o "No Funcional".',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser "Activo", "Inactivo" o "Mantenimiento".',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
        ]);

        if (
            $request->cod_inventario &&
            Equipo::where('cod_inventario', $request->cod_inventario)
                ->where('id', '!=', $equipo->id)
                ->exists()
        ) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Ya existe un equipo con el mismo código de inventario.',
                    'type' => 'warning',
                ],
            ]);
        }

        if (
            $request->filled('num_serie') &&
            CaracteristicaEquipo::where('nombre', 'num_serie')
                ->where('valor', $request->num_serie)
                ->where('id_equipo', '!=', $equipo->id)
                ->exists()
        ) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Ya existe un equipo con el mismo número de serie.',
                    'type' => 'warning',
                ],
            ]);
        }

        try {
            DB::beginTransaction();

            $oldAreaId = $equipo->id_area;
            $newAreaId = $request->id_area;

            $equipo->id_usuario = $request->id_usuario;
            $equipo->id_area = $newAreaId;
            $equipo->estado = $request->estado;
            $equipo->tipo = $request->tipo;
            $equipo->cod_inventario = $request->cod_inventario;
            $equipo->nombre = $request->nombre;
            $equipo->save();

            // Gestionar historial de áreas si cambió el área
            if ($oldAreaId != $newAreaId) {
                if ($oldAreaId) {
                    HistorialAreaEquipo::where('id_equipo', $equipo->id)
                        ->where('id_area', $oldAreaId)
                        ->whereNull('fecha_fin')
                        ->update(['fecha_fin' => now()]);
                }

                if ($newAreaId) {
                    HistorialAreaEquipo::create([
                        'id_equipo' => $equipo->id,
                        'id_area' => $newAreaId,
                        'fecha_asignacion' => now(),
                        'observacion' => $request->observacion_cambio_area ?? 'Cambio de área del equipo',
                    ]);
                }
            }

            $caracteristics = [
                'marca' => $request->marca,
                'modelo' => $request->modelo,
                'num_serie' => $request->num_serie,
                'estado_funcional' => $request->estado_funcional,
            ];

            $allowedCaracteristicas = array_keys($caracteristics);
            foreach (($request->caracteristicas ?? []) as $key => $value) {
                if (!in_array($key, $allowedCaracteristicas, true)) {
                    $allowedCaracteristicas[] = $key;
                }
            }

            foreach ($caracteristics as $key => $value) {
                CaracteristicaEquipo::updateOrCreate(
                    [
                        'id_equipo' => $equipo->id,
                        'nombre' => $key,
                    ],
                    [
                        'valor' => $value ?? '',
                    ]
                );
            }
            foreach (($request->caracteristicas) as $key => $value) {
                if(!in_array($key, array_keys($caracteristics), true)){
                    CaracteristicaEquipo::updateOrCreate(
                        [
                            'id_equipo' => $equipo->id,
                            'nombre' => $key,
                        ],
                        [
                            'valor' => $value ?? '',
                        ]
                    );
                }
            }

            CaracteristicaEquipo::where('id_equipo', $equipo->id)
                ->whereNotIn('nombre', $allowedCaracteristicas)
                ->delete();

            $allowedComponentes = array_keys($request->componentes ?? []);
            $componentes = Componente::where('id_equipo', $equipo->id)
            ->whereNotIn('nombre', $allowedComponentes)
            ->get();

            foreach ($componentes as $componente) {
                if (!in_array($componente->nombre, $allowedComponentes, true)) {
                    $componente->caracteristicas()->delete();
                    $componente->delete();
                    continue;
                }

                $submittedCaracteristicas = $request->componentes[$componente->nombre] ?? [];
                $allowedComponentCaracteristicas = array_keys($submittedCaracteristicas);

                foreach ($submittedCaracteristicas as $key2 => $value2) {
                    CaracteristicaComponente::updateOrCreate(
                        [
                            'id_componente' => $componente->id,
                            'nombre' => $key2,
                        ],
                        [
                            'valor' => $value2 ?? '',
                        ]
                    );
                }

                $componente->caracteristicas()
                    ->whereNotIn('nombre', $allowedComponentCaracteristicas)
                    ->delete();
            }

            foreach (($request->componentes ?? []) as $key => $value) {
                if (count($value) > 0) {
                    $component = Componente::updateOrCreate(
                        [
                            'id_equipo' => $equipo->id,
                            'nombre' => $key,
                        ],
                        []
                    );

                    foreach ($value as $key2 => $value2) {
                        CaracteristicaComponente::updateOrCreate(
                            [
                                'id_componente' => $component->id,
                                'nombre' => $key2,
                            ],
                            [
                            'valor' => $value2 ?? '',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Equipo actualizado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al actualizar el equipo',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function delete(Equipo $equipo)
    {
        if($equipo->delete()){
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Equipo eliminado exitosamente',
                    'type' => 'success',
                ],
            ]);
        }
        return redirect()->back()->with([
            'message' => [
                'message' => 'Error al eliminar el equipo',
                'type' => 'error',
            ],
        ]);
    }

    public function addComment(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'comentario' => 'required|string|max:1000',
        ]);

        try {
            EquipoComentario::create([
                'equipo_id' => $request->equipo_id,
                'comentario' => $request->comentario,
            ]);

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Comentario agregado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al agregar el comentario',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function deleteComment(EquipoComentario $comentario)
    {
        try {
            $comentario->delete();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Comentario eliminado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al eliminar el comentario',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function addFile(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'nombre' => 'required|string|max:255',
            'archivo' => 'required|file|max:51200', // 50MB
        ]);

        try {
            if(!$request->hasFile('archivo')) {
                return redirect()->back()->with([
                    'message' => [
                        'message' => 'No se ha seleccionado ningún archivo',
                        'type' => 'warning',
                    ],
                ]);
            }

            $file = $request->file('archivo');
            $path = $file->store('equipo_archivos/' . $request->equipo_id, 'public');

            $equipoArchivo = new EquipoArchivo();
            $equipoArchivo->equipo_id = $request->equipo_id;
            $equipoArchivo->nombre = $request->nombre;
            $equipoArchivo->nombre_interno = $path;
            $equipoArchivo->save();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Archivo agregado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al agregar el archivo',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function deleteFile(EquipoArchivo $archivo)
    {
        try {
            $dir = $archivo->nombre_interno;
            if(Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->delete($dir);
            }

            $archivo->delete();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Archivo eliminado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al eliminar el archivo',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function downloadFile(EquipoArchivo $archivo)
    {
        try {
            $dir = $archivo->nombre_interno;
            if(Storage::disk('public')->exists($dir)) {
                $downloadName = $archivo->nombre . '.' . pathinfo($dir, PATHINFO_EXTENSION);
                return Storage::disk('public')->download($dir, $downloadName);
            } else {
                return redirect()->back()->with([
                    'message' => [
                        'message' => 'El archivo no existe',
                        'type' => 'error',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al descargar el archivo',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function addMaintenance(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'id_encargado' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('it_staff', true)->where('estado', 'activo');
                }),
            ],
            'fecha' => 'required|date',
            'observacion' => 'required|string|max:2000',
        ], [
            'fecha.required' => 'La fecha del mantenimiento es obligatoria.',
            'observacion.required' => 'La observación del mantenimiento es obligatoria.',
            'id_encargado.exists' => 'El técnico seleccionado debe pertenecer al personal de TI y estar activo.',
        ]);

        try {
            Mantenimiento::create([
                'id_equipo' => $request->equipo_id,
                'id_encargado' => $request->id_encargado ?? auth()->id(),
                'fecha' => $request->fecha,
                'observacion' => $request->observacion,
            ]);

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Mantenimiento registrado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al registrar el mantenimiento',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function deleteMaintenance(Mantenimiento $mantenimiento)
    {
        try {
            $mantenimiento->delete();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Mantenimiento eliminado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al eliminar el mantenimiento',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function addPeripheral(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'tipo' => 'required|string|max:100',
            'marca' => 'nullable|string|max:150',
            'modelo' => 'nullable|string|max:150',
            'num_serie' => 'nullable|string|max:150',
            'cod_inventario' => 'nullable|string|max:100',
            'estado' => 'required|in:operativo,en_revision,danado,obsoleto',
            'observacion' => 'nullable|string|max:1000',
            'imagen' => 'nullable|image|max:10240',
        ], [
            'tipo.required' => 'El tipo de periférico es obligatorio.',
            'estado.required' => 'El estado del periférico es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'imagen.image' => 'El archivo seleccionado debe ser una imagen.',
            'imagen.max' => 'La imagen no debe superar los 10MB.',
        ]);

        try {
            $imagenPath = null;
            if ($request->hasFile('imagen')) {
                $imagenPath = $request->file('imagen')->store('equipo_perifericos/' . $request->equipo_id, 'public');
            }

            EquipoPeriferico::create([
                'equipo_id' => $request->equipo_id,
                'tipo' => $request->tipo,
                'marca' => $request->marca,
                'modelo' => $request->modelo,
                'num_serie' => $request->num_serie,
                'cod_inventario' => $request->cod_inventario,
                'estado' => $request->estado,
                'observacion' => $request->observacion,
                'imagen' => $imagenPath,
            ]);

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Periférico registrado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al registrar el periférico',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function updatePeripheral(Request $request, EquipoPeriferico $periferico)
    {
        $request->validate([
            'tipo' => 'required|string|max:100',
            'marca' => 'nullable|string|max:150',
            'modelo' => 'nullable|string|max:150',
            'num_serie' => 'nullable|string|max:150',
            'cod_inventario' => 'nullable|string|max:100',
            'estado' => 'required|in:operativo,en_revision,danado,obsoleto',
            'observacion' => 'nullable|string|max:1000',
            'imagen' => 'nullable|image|max:10240',
            'remove_imagen' => 'nullable|boolean',
        ], [
            'tipo.required' => 'El tipo de periférico es obligatorio.',
            'estado.required' => 'El estado del periférico es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'imagen.image' => 'El archivo seleccionado debe ser una imagen.',
            'imagen.max' => 'La imagen no debe superar los 10MB.',
        ]);

        try {
            if ($request->hasFile('imagen')) {
                if ($periferico->imagen && Storage::disk('public')->exists($periferico->imagen)) {
                    Storage::disk('public')->delete($periferico->imagen);
                }
                $periferico->imagen = $request->file('imagen')->store('equipo_perifericos/' . $periferico->equipo_id, 'public');
            } elseif ($request->boolean('remove_imagen')) {
                if ($periferico->imagen && Storage::disk('public')->exists($periferico->imagen)) {
                    Storage::disk('public')->delete($periferico->imagen);
                }
                $periferico->imagen = null;
            }

            $periferico->tipo = $request->tipo;
            $periferico->marca = $request->marca;
            $periferico->modelo = $request->modelo;
            $periferico->num_serie = $request->num_serie;
            $periferico->cod_inventario = $request->cod_inventario;
            $periferico->estado = $request->estado;
            $periferico->observacion = $request->observacion;
            $periferico->save();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Periférico actualizado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al actualizar el periférico',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function deletePeripheral(EquipoPeriferico $periferico)
    {
        try {
            if ($periferico->imagen && Storage::disk('public')->exists($periferico->imagen)) {
                Storage::disk('public')->delete($periferico->imagen);
            }

            $periferico->delete();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Periférico eliminado exitosamente',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al eliminar el periférico',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function addLoan(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'id_area_destino' => 'required|exists:areas,id',
            'id_usuario_responsable' => 'nullable|exists:users,id',
            'fecha_prestamo' => 'required|date',
            'fecha_devolucion_estimada' => 'nullable|date|after_or_equal:fecha_prestamo',
            'motivo' => 'required|string|max:1000',
        ], [
            'id_area_destino.required' => 'El área de destino es obligatoria.',
            'id_area_destino.exists' => 'El área de destino seleccionada no es válida.',
            'fecha_prestamo.required' => 'La fecha de préstamo es obligatoria.',
            'fecha_devolucion_estimada.after_or_equal' => 'La fecha estimada de devolución debe ser igual o posterior a la fecha del préstamo.',
            'motivo.required' => 'El motivo del préstamo es obligatorio.',
        ]);

        $equipo = Equipo::findOrFail($request->equipo_id);

        if ($equipo->prestamoActivo) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Este equipo ya se encuentra actualmente en calidad de préstamo.',
                    'type' => 'warning',
                ],
            ]);
        }

        if ($equipo->id_area && (int)$equipo->id_area === (int)$request->id_area_destino) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'El área de destino no puede ser la misma área propietaria del equipo.',
                    'type' => 'warning',
                ],
            ]);
        }

        try {
            DB::beginTransaction();

            EquipoPrestamo::create([
                'id_equipo' => $equipo->id,
                'id_area_origen' => $equipo->id_area,
                'id_area_destino' => $request->id_area_destino,
                'id_usuario_responsable' => $request->id_usuario_responsable,
                'fecha_prestamo' => $request->fecha_prestamo,
                'fecha_devolucion_estimada' => $request->fecha_devolucion_estimada,
                'estado' => 'activo',
                'motivo' => $request->motivo,
            ]);

            DB::commit();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Préstamo del equipo registrado exitosamente.',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al registrar el préstamo del equipo.',
                    'type' => 'error',
                ],
            ]);
        }
    }

    public function returnLoan(Request $request, EquipoPrestamo $prestamo)
    {
        $request->validate([
            'fecha_devolucion_real' => 'required|date',
            'observacion_devolucion' => 'nullable|string|max:1000',
        ], [
            'fecha_devolucion_real.required' => 'La fecha de devolución es obligatoria.',
        ]);

        if ($prestamo->estado === 'devuelto' || $prestamo->fecha_devolucion_real) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Este préstamo ya fue marcado como devuelto previamente.',
                    'type' => 'warning',
                ],
            ]);
        }

        try {
            DB::beginTransaction();

            $prestamo->fecha_devolucion_real = $request->fecha_devolucion_real;
            $prestamo->observacion_devolucion = $request->observacion_devolucion;
            $prestamo->estado = 'devuelto';
            $prestamo->save();

            DB::commit();

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Devolución del equipo registrada exitosamente.',
                    'type' => 'success',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al registrar la devolución del equipo.',
                    'type' => 'error',
                ],
            ]);
        }
    }
}
