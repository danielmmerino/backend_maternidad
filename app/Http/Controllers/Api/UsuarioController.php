<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

class UsuarioController extends Controller
{
    public function store(StoreUsuarioRequest $request)
    {
        $data = $request->validated();

        // Defaults y transformaciones
        $data['public_id'] = $data['public_id'] ?? (string) Str::uuid();
        $data['id_estado'] = $data['id_estado'] ?? 1;
        $data['password']  = Hash::make($data['password']);

         try {
        $usuario = DB::transaction(function () use ($data) {
            // 1) Crear usuario
            $usuario = Usuario::create([
                'public_id'      => $data['public_id'],
                'identificacion' => $data['identificacion'],
                'nombre'         => $data['nombre'],
                'id_estado'      => $data['id_estado'],
                'correo'         => $data['correo'],
                'password'       => $data['password'],
            ]);

            // 2) Insertar relación usuario–perfil
            DB::table('usuarios_perfil_usuario')->insert([
                'id_usuario'     => $usuario->id,
                'id_perfil'      => $data['id_perfil'],
                'id_estado'      => 1, // activo
                'fecha_creacion' => now(),
            ]);

            return $usuario;
        });

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data' => [
                'public_id'      => $usuario->public_id,
                'identificacion' => $usuario->identificacion,
                'nombre'         => $usuario->nombre,
                'correo'         => $usuario->correo,
                'id_estado'      => $usuario->id_estado,
                'perfil_asignado'=> (int) $request->input('id_perfil'),
            ],
        ], Response::HTTP_CREATED);

    } catch (QueryException $e) {
        $errno = $e->errorInfo[1] ?? null; // MySQL errno

        if ($errno === 1062) {
            return response()->json([
                'message' => 'No se pudo crear el usuario',
                'error'   => 'Duplicado: identificación o correo ya existen'
            ], 409);
        }

        if ($errno === 1452) {
            return response()->json([
                'message' => 'No se pudo crear el usuario',
                'error'   => 'Violación de clave foránea (verifica id_estado o id_perfil existen)'
            ], 409);
        }

        return response()->json([
            'message' => 'No se pudo crear el usuario',
            'error'   => 'Error de base de datos',
            // 'details' => $e->getMessage(), // descomenta durante desarrollo si necesitas ver el detalle
        ], 500);
    }
    }
}
