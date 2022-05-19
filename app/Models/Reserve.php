<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $fillable = [
        'horaInicio',
        'horaFinalizacion',
        'fechaCita',
        'numLista',
        'users_id',
        'pistas_id'
    ];
}
