<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgendaSaludRequest;
use App\Models\Usuario;
use App\Models\UsuarioAgendaSalud;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

class AgendaSaludController extends Controller
{
    public function store(StoreAgendaSaludRequest $request)
    {
        $data = $request->validated();

        $usuario = Usuario::where('public_id', $data['public_id'])->first();

        if (!$usuario) {
            return response()->json([
                'message' => 'No se pudo crear la agenda',
                'error' => 'El usuario especificado no existe',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $agenda = UsuarioAgendaSalud::create([
                'id_usuario' => $usuario->id,
                'nombre_completo' => $data['nombres_completo'],
                'id_nacionalidad' => $data['id_nacionalidad'],
                'rut' => $data['rut'],
                'fecha_nacimiento' => Carbon::parse($data['fecha_nacimiento']),
                'es_originario' => $data['es_originario'],
                'descripcion_originario' => $data['descripcion_originario'] ?? 'NA',
                'telefono1' => $data['telefono1'],
                'telefono2' => $data['telefono2'],
                'correo_electronico' => $data['correo_electronico'],
                'ocupacion' => $data['ocupacion'],
                'domicilio' => $data['domicilio'],
                'escolaridad_basica' => $data['escolaridad_basica'],
                'escolaridad_media' => $data['escolaridad_media'],
                'escolaridad_superior' => $data['escolaridad_superior'],
                'id_estado' => 1,
            ]);

            return response()->json([
                'message' => 'Agenda creada correctamente',
                'data' => [
                    'id' => $agenda->id,
                    'nombre_completo' => $agenda->nombre_completo,
                    'correo_electronico' => $agenda->correo_electronico,
                    'id_estado' => $agenda->id_estado,
                ],
            ], Response::HTTP_CREATED);
        } catch (QueryException $exception) {
            return response()->json([
                'message' => 'No se pudo crear la agenda',
                'error' => 'Error de base de datos',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
