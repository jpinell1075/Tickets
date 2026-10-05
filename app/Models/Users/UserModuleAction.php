<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;

class UserModuleAction extends Model
{
    use UserStampsTrait;

    protected $table = 'users_modules_actions';

    protected $fillable = [
        'user_module_id',
        'action',
    ];


    protected $appends = [
        'action_name',
    ];

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
    
    public function userModule()
    {
        return $this->belongsTo(UserModule::class, 'user_module_id');
    }
}
