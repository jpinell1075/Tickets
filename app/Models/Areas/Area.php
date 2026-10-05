<?php

namespace App\Models\Areas;

use App\Models\User;
use App\Models\Equipos\Equipo;
use App\Models\Equipos\EquipoPrestamo;
use App\Models\Areas\HistorialEncargadoArea;
use App\Models\Tickets\PlanificacionMantenimiento;
use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TableFormData\Inventory\Area as AreaTableFormDataTrait;

class Area extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait, AreaTableFormDataTrait;

    protected $table = 'areas';

    protected $fillable = [
        'id_usuario_encargado',
        'estado',
        'nombre',
        'descripcion',
    ];

    public function encargado()
    {
        return $this->belongsTo(User::class, 'id_usuario_encargado');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_area');
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_area');
    }

    public function planificacionesMantenimiento()
    {
        return $this->hasMany(PlanificacionMantenimiento::class, 'id_area');
    }

    public function historialEncargados()
    {
        return $this->hasMany(HistorialEncargadoArea::class, 'id_area');
    }

    public function equiposPrestados()
    {
        return $this->hasMany(EquipoPrestamo::class, 'id_area_origen')->where('estado', 'activo');
    }

    public function equiposRecibidos()
    {
        return $this->hasMany(EquipoPrestamo::class, 'id_area_destino')->where('estado', 'activo');
    }
}