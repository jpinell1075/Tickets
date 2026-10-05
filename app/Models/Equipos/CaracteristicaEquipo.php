<?php

namespace App\Models\Equipos;

use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaracteristicaEquipo extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait;

    protected $table = 'caracteristicas_equipos';

    protected $fillable = [
        'id_equipo',
        'nombre',
        'valor',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }
}