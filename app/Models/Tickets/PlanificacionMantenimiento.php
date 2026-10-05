<?php

namespace App\Models\Tickets;

use App\Models\Areas\Area;
use App\Models\Tickets\Ticket;
use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TableFormData\Maintenances\PlanificacionMantenimiento as PlanificacionMantenimientoTrait;

class PlanificacionMantenimiento extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait, PlanificacionMantenimientoTrait;

    protected $table = 'planificacion_mantenimientos';

    protected $fillable = [
        'id_area',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_fin' => 'date:Y-m-d',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_programacion_mantenimiento');
    }
}