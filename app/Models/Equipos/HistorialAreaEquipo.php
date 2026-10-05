<?php

namespace App\Models\Equipos;

use App\Models\Areas\Area;
use App\Models\User;
use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialAreaEquipo extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait;

    protected $table = 'historial_areas_equipos';

    protected $fillable = [
        'id_equipo',
        'id_area',
        'fecha_asignacion',
        'fecha_fin',
        'observacion',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

