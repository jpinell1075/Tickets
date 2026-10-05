<?php

namespace App\Models\Tickets;

use App\Models\Equipos\Equipo;
use App\Models\User;
use App\Models\Tickets\Ticket;
use App\Traits\ModelSoftDeleteTrait;
use Everth\UserStamps\UserStampsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    use HasFactory, ModelSoftDeleteTrait, UserStampsTrait;

    protected $table = 'mantenimientos';

    protected $fillable = [
        'id_ticket',
        'id_equipo',
        'id_encargado',
        'tipo',
        'fecha',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }

    public function encargado()
    {
        return $this->belongsTo(User::class, 'id_encargado');
    }
}