<?php

namespace App\Models\Equipos;

use App\Models\Areas\Area;
use App\Models\Tickets\Mantenimiento;
use App\Models\Tickets\Ticket;
use App\Models\User;
use App\Models\Equipos\CaracteristicaEquipo;
use App\Models\Equipos\Componente;
use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TableFormData\Inventory\Equipo as EquipoTableFormDataTrait;

class Equipo extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait, EquipoTableFormDataTrait;

    protected $table = 'equipos';

    protected $fillable = [
        'id_usuario',
        'id_area',
        'estado',
        'tipo',
        'cod_inventario',
        'nombre',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function caracteristicas()
    {
        return $this->hasMany(CaracteristicaEquipo::class, 'id_equipo');
    }

    public function componentes()
    {
        return $this->hasMany(Componente::class, 'id_equipo');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_equipo');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_equipo');
    }

    public function comentarios()
    {
        return $this->hasMany(EquipoComentario::class, 'equipo_id');
    }

    public function archivos()
    {
        return $this->hasMany(EquipoArchivo::class, 'equipo_id');
    }

    public function perifericos()
    {
        return $this->hasMany(EquipoPeriferico::class, 'equipo_id');
    }

    public function historialAreas()
    {
        return $this->hasMany(HistorialAreaEquipo::class, 'id_equipo');
    }

    public function prestamos()
    {
        return $this->hasMany(EquipoPrestamo::class, 'id_equipo');
    }

    public function prestamoActivo()
    {
        return $this->hasOne(EquipoPrestamo::class, 'id_equipo')
            ->where('estado', 'activo')
            ->whereNull('fecha_devolucion_real')
            ->latestOfMany();
    }
}