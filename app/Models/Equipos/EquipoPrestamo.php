<?php

namespace App\Models\Equipos;

use App\Models\Areas\Area;
use App\Models\User;
use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipoPrestamo extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait;

    protected $table = 'equipo_prestamos';

    protected $fillable = [
        'id_equipo',
        'id_area_origen',
        'id_area_destino',
        'id_usuario_responsable',
        'fecha_prestamo',
        'fecha_devolucion_estimada',
        'fecha_devolucion_real',
        'estado',
        'motivo',
        'observacion_devolucion',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }

    public function areaOrigen()
    {
        return $this->belongsTo(Area::class, 'id_area_origen');
    }

    public function areaDestino()
    {
        return $this->belongsTo(Area::class, 'id_area_destino');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'id_usuario_responsable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

