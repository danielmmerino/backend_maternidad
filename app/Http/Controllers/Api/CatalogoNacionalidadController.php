<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CatalogoNacionalidadController extends Controller
{
    public function index()
    {
        $nacionalidades = DB::table('catalogo_nacionalidad')
            ->select('id', 'nombre_nacionalidad')
            ->where('id_estado', 1)
            ->orderBy('nombre_nacionalidad')
            ->get();

        return response()->json([
            'data' => $nacionalidades,
        ], Response::HTTP_OK);
    }
}
