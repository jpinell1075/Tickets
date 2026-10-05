<?php

namespace App\Models\Equipos;

use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaracteristicaComponente extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait;

    protected $table = 'caracteristicas_componentes';

    protected $fillable = [
        'id_componente',
        'nombre',
        'valor',
    ];

    public function componente()
    {
        return $this->belongsTo(Componente::class, 'id_componente');
    }
}