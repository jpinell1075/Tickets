<?php

namespace App\Models\Equipos;

use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;

class EquipoComentario extends Model
{
    use UserStampsTrait;

    protected $table = 'equipo_comentarios';

    protected $fillable = [
        'equipo_id',
        'comentario',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
