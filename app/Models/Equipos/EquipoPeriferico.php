<?php

namespace App\Models\Equipos;

use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EquipoPeriferico extends Model
{
    use HasFactory, UserStampsTrait;

    protected $table = 'equipo_perifericos';

    protected $fillable = [
        'equipo_id',
        'tipo',
        'marca',
        'modelo',
        'num_serie',
        'cod_inventario',
        'estado',
        'observacion',
        'imagen',
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

