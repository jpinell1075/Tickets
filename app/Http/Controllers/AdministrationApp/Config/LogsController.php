<?php

namespace App\Http\Controllers\AdministrationApp\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseFormRequest;
use Inertia\Inertia;
use App\Models\Configurations\UserLog;

class LogsController extends Controller
{
    public function index()
    {
        return Inertia::render('administration_app.config.logs', [
            'model' => UserLog::getStaticData(),
        ]);
    }

    public function cleanUserLogs()
    {
        try{
            $user_logs_file = storage_path('logs/user_actions.log');
            if (file_exists($user_logs_file)) {
                file_put_contents($user_logs_file, '');
            }

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Logs de usuario limpiados correctamente',
                    'type' => 'success'
                ]
            ]);
        }
        catch(\Exception $e){
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al limpiar los logs de usuario',
                    'type' => 'danger'
                ]
            ]);
        }
    }
}
