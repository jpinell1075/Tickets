<?php

namespace App\Models\Users\Templates;

use Illuminate\Database\Eloquent\Model;

class UserTemplateModuleAction extends Model
{
    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_template_module_id',
        'action',
    ];

    protected $appends = [
        'action_name',
    ];

    /**
     * Defines a many-to-one relationship with the UserTemplateModule model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userTemplateModule()
    {
        return $this->belongsTo(UserTemplateModule::class);
    }

    /**
     * Get the name of the action.
     *
     * @return string
     */
    public function getActionNameAttribute()
    {
        $actionNames = [
            'read' => 'Leer',
            'search' => 'Busqueda',
            'create' => 'Crear',
            'update' => 'Actualizar',
            'delete' => 'Eliminar',
        ];

        return $actionNames[$this->action] ?? '';
    }
}
