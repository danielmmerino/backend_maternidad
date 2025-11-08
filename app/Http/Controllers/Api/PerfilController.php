<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    public function validarPerfilDoctor(Request $request)
    {
        $data = $request->validate([
            'public_id' => ['required', 'uuid'],
            'public_id_perfil' => ['required', 'uuid'],
        ]);

        $exists = DB::table('usuarios_usuario as uu')
            ->join('usuarios_perfil_usuario as upu', 'uu.id', '=', 'upu.id_usuario')
            ->join('catalogo_perfil as cp', 'upu.id_perfil', '=', 'cp.id')
            ->where('uu.public_id', $data['public_id'])
            ->where('cp.public_id_perfil', $data['public_id_perfil'])
            ->where('upu.id_estado', 1)
            ->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }
}
