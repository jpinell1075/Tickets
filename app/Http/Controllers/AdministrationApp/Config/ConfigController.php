<?php

namespace App\Http\Controllers\AdministrationApp\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseFormRequest;
use Inertia\Inertia;
use App\Models\Configurations\Configuration;
use Illuminate\Support\Facades\Cache;

class ConfigController extends Controller
{
    public function index()
    {
        return Inertia::render('administration_app.config.index', [
            'configurations' => Configuration::whereNotIn('key', ['global_use_soft_deletes', 'global_use_social_login'])->get(),
        ]);
    }

    public function update(BaseFormRequest $request, Configuration $configuration)
    {
        $validations = [
            'string' => 'nullable|string|max:255',
            'integer' => 'nullable|integer',
            'boolean' => 'nullable|boolean',
            'json' => 'nullable|string|max:65535',
        ];

        $request->validate([
            'value' => $validations[$configuration->type] ?? 'nullable|string|max:65535',
        ]);

        $value = $request->input('value');

        switch ($configuration->type) {
            case 'boolean':
                $value = $value ? 'true' : 'false';
                break;
            case 'integer':
                $value = (string) intval($value);
                break;
            case 'json':
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()->with([
                        'message' => [
                            'message' => 'El valor proporcionado no es un JSON válido.',
                            'type' => 'danger'
                        ],
                    ]);
                }
                $value = json_encode($decoded);
                break;
            case 'string':
            default:
                $value = (string) $value;
                break;
        }

        $configuration->value = $value;

        if ($configuration->key === 'global_use_logs') {
            Cache::forget('global_use_logs');
        }

        if($configuration->save()) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Configuración actualizada correctamente.',
                    'type' => 'success'
                ],
            ]);
        } else {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al actualizar la configuración.',
                    'type' => 'danger'
                ],
            ]);
        }
    }
}
