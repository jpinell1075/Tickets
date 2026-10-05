<?php

namespace App\Models\Areas;

use App\Models\User;
use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEncargadoArea extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait;

    protected $table = 'historial_encargados_areas';

    protected $fillable = [
        'id_area',
        'id_usuario_encargado',
        'fecha_asignacion',
        'fecha_fin',
        'observacion',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function encargado()
    {
        return $this->belongsTo(User::class, 'id_usuario_encargado');
    }
}