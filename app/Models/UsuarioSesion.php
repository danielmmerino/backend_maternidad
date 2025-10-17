<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioSesion extends Model
{
    protected $table = 'usuarios_sesiones_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'jwt_id',
        'token_hash',
        'refresh_token_hash',
        'issued_at',
        'expires_at',
        'revoked_at',
        'revocation_reason',
        'ip_address',
        'user_agent',
        'device_info',
        'id_estado',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];
}
