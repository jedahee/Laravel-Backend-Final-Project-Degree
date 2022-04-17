<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'suelos';

    protected $fillable = [
        'nombre',
    ];

    // Relaciones
    public function courts()
    {
        return $this->hasMany(Court::class);
    }
}
