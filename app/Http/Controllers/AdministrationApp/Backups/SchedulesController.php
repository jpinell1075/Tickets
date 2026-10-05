<?php

namespace App\Http\Controllers\AdministrationApp\Backups;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseFormRequest;
use App\Models\Backups\ScheduledBackup;

class SchedulesController extends Controller
{
    public function update(BaseFormRequest $request)
    {
        $request->validate([
            'days' => 'nullable|array',
            'hours' => 'nullable|array',
        ], [
            'days.array' => 'El campo de días debe ser un arreglo.',
            'hours.array' => 'El campo de horas debe ser un arreglo.',
        ]);

        try{
            $backup = ScheduledBackup::firstOrNew(['id' => 1]);

            $backup->days = array_map('intval', $request->days);

            $backup->times = $request->hours;

            $backup->active = $request->active ? true : false;
            $backup->save();

            return back()->with([
                'message' => [
                    'message' => 'La tarea de respaldo fue actualizada correctamente.',
                    'type' => 'success',
                ],
            ]);

        } catch(\Exception $e) {
            return back()->with([
                'message' => [
                    'message' => 'Error al programar el respaldo.',
                    'type' => 'danger'
                ],
            ]);
        }
    }
}
