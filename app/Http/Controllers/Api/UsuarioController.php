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
                return Usuario::create($data);
            });

            return response()->json([
                'message' => 'Usuario creado correctamente',
                'data' => [
                    'id'             => $usuario->id,
                    'public_id'      => $usuario->public_id,
                    'identificacion' => $usuario->identificacion,
                    'nombre'         => $usuario->nombre,
                    'correo'         => $usuario->correo,
                    'id_estado'      => $usuario->id_estado,
                    'fecha_creacion' => $usuario->fecha_creacion ?? null, // lo pone la BD
                ],
            ], Response::HTTP_CREATED);

        } catch (QueryException $e) {
            // Por si la BD tiene unique y explota por duplicado
            return response()->json([
                'message' => 'No se pudo crear el usuario',
                'error'   => $e->getCode() === '23000'
                    ? 'Duplicado: identificación o correo ya existen'
                    : 'Error de base de datos',
            ], Response::HTTP_CONFLICT);
        }
    }
}
