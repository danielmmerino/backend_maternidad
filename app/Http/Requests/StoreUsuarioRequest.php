<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajusta si luego proteges con auth/roles
    }

    public function rules(): array
    {
        return [
            'identificacion' => ['required','string','max:50','unique:usuarios_usuario,identificacion'],
            'nombre'         => ['required','string','max:50'],
            'correo'         => ['required','email','max:120','unique:usuarios_usuario,correo'],
            'password'       => ['required','string','min:8'],
            'id_estado'      => ['nullable','integer','exists:catalogo_estados,id'], // por defecto 
             'id_perfil'      => ['required','integer','exists:catalogo_perfil,id'],
        ];
    }
}
