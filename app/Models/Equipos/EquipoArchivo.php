<?php

namespace App\Models\Equipos;

use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;

class EquipoArchivo extends Model
{
    use UserStampsTrait;

    protected $table = 'equipo_archivos';

    protected $fillable = [
        'equipo_id',
        'nombre',
        'nombre_interno',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
