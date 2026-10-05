<?php

namespace App\Models\Tickets;

use App\Models\Equipos\Equipo;
use App\Models\User;
use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait;

    protected $table = 'tickets';

    protected $fillable = [
        'id_equipo',
        'id_usuario',
        'id_encargado',
        'id_programacion_mantenimiento',
        'tipo', // 'hardware', 'software', 'network', 'other'
        'prioridad', // 'baja', 'media', 'alta', 'critica'
        'titulo',
        'descripcion',
        'estado_actual', // 'abierto', 'en_proceso', 'resuelto', 'cerrado', 'cancelado'
        'fecha_creacion',
        'observacion_resolucion',
        'fecha_resolucion',
        'fecha_planificada',  // fecha en la que se debe realizar el mantenimiento segun planificacion
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function encargado()
    {
        return $this->belongsTo(User::class, 'id_encargado');
    }

    public function programacionMantenimiento()
    {
        return $this->belongsTo(PlanificacionMantenimiento::class, 'id_programacion_mantenimiento');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_ticket');
    }
}