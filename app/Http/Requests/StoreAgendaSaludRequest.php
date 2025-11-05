<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgendaSaludRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'public_id' => ['required', 'uuid', Rule::exists('usuarios_usuario', 'public_id')],
            'nombres_completo' => ['required', 'string', 'max:200'],
            'id_nacionalidad' => ['required', 'integer', Rule::exists('catalogo_nacionalidad', 'id')],
            'rut' => ['required', 'string', 'max:200'],
            'fecha_nacimiento' => ['required', 'date'],
            'es_originario' => ['required', 'boolean'],
            'descripcion_originario' => ['nullable', 'string', 'max:200'],
            'telefono1' => ['required', 'string', 'max:50'],
            'telefono2' => ['required', 'string', 'max:50'],
            'correo_electronico' => ['required', 'string', 'email', 'max:100'],
            'ocupacion' => ['required', 'string', 'max:100'],
            'domicilio' => ['required', 'string', 'max:200'],
            'escolaridad_basica' => ['required', 'boolean'],
            'escolaridad_media' => ['required', 'boolean'],
            'escolaridad_superior' => ['required', 'boolean'],
        ];
    }
}
