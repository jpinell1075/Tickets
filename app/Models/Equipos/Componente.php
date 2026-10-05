<?php

namespace App\Models\Equipos;

use App\Models\Equipos\CaracteristicaComponente;
use App\Models\Equipos\Equipo;
use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Componente extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait;

    protected $table = 'componentes';

    protected $fillable = [
        'id_equipo',
        'nombre',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }

    public function caracteristicas()
    {
        return $this->hasMany(CaracteristicaComponente::class, 'id_componente');
    }
}