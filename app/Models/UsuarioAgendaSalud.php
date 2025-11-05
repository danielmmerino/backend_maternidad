<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioAgendaSalud extends Model
{
    protected $table = 'usuarios_usuario_agenda_salud';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombre_completo',
        'id_nacionalidad',
        'rut',
        'fecha_nacimiento',
        'es_originario',
        'descripcion_originario',
        'telefono1',
        'telefono2',
        'correo_electronico',
        'ocupacion',
        'domicilio',
        'escolaridad_basica',
        'escolaridad_media',
        'escolaridad_superior',
        'id_estado',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'datetime',
        'es_originario' => 'boolean',
        'escolaridad_basica' => 'boolean',
        'escolaridad_media' => 'boolean',
        'escolaridad_superior' => 'boolean',
    ];
}
