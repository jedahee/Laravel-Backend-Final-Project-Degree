<?php

namespace App\Models;

use Deportes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'pistas'; 

    protected $fillable = [
        'nombre',
//        'horaInicio',
//        'horaFinalizacion',
        'rutaImagen',
        'direccion',
        'aforo',
        'precioPorHora',
        'disponible',
        'campoAbierto',
        'iluminacion',
        'suelo_id',
        'deporte_id'
    ];

    // Relaciones

    public function reserves()
    {
        return $this->hasMany(Reserve::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }
}
