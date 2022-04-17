<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'deportes';

    protected $fillable = [
        'nombre',
    ];

    // Relaciones
    public function courts()
    {
        return $this->hasMany(Court::class);
    }
}
