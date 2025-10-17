<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios_usuario';
    public $timestamps = false; // la tabla usa fecha_creacion propia

    protected $fillable = [
        'public_id',
        'identificacion',
        'nombre',
        'id_estado',
        'correo',
        'password',
    ];

    protected $hidden = ['password'];
}
