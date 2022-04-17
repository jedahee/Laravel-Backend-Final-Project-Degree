<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'reservas';

    protected $fillable = [
        'nombre',
        'users_id',
        'pistas_id'
    ];
}
