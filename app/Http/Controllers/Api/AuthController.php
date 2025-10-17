<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Usuario;
use App\Models\UsuarioSesion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $usuario = Usuario::where('correo', $credentials['correo'])
            ->where('id_estado', 1)
            ->first();

        if (! $usuario || ! Hash::check($credentials['password'], $usuario->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $perfil = DB::table('usuarios_usuario as uu')
            ->join('usuarios_perfil_usuario as upu', 'uu.id', '=', 'upu.id_usuario')
            ->join('catalogo_perfil as cp', 'upu.id_perfil', '=', 'cp.id')
            ->where('uu.id', $usuario->id)
            ->where('upu.id_estado', 1)
            ->where('uu.id_estado', 1)
            ->select('uu.public_id', 'uu.nombre', 'uu.correo', 'cp.nombre_perfil')
            ->first();

        if (! $perfil) {
            return response()->json([
                'message' => 'El usuario no tiene un perfil activo asignado',
            ], Response::HTTP_FORBIDDEN);
        }

        $issuedAt = now();
        $expiresAt = $issuedAt->copy()->addDay();
        $jwtId = (string) Str::uuid();

        $token = $this->generateJwtToken([
            'sub' => $usuario->public_id,
            'iat' => $issuedAt->timestamp,
            'exp' => $expiresAt->timestamp,
            'jti' => $jwtId,
        ]);

        UsuarioSesion::create([
            'id_usuario' => $usuario->id,
            'jwt_id' => $jwtId,
            'token_hash' => hash('sha256', $token),
            'refresh_token_hash' => null,
            'issued_at' => $issuedAt,
            'expires_at' => $expiresAt,
            'revoked_at' => null,
            'revocation_reason' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_info' => $request->userAgent(),
            'id_estado' => 1,
            'fecha_creacion' => $issuedAt,
            'fecha_actualizacion' => $issuedAt,
        ]);

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'public_id' => $perfil->public_id,
                'nombre' => $perfil->nombre,
                'correo' => $perfil->correo,
                'perfil' => $perfil->nombre_perfil,
                'token' => $token,
                'expira_en' => $expiresAt->toIso8601String(),
            ],
        ]);
    }

    private function generateJwtToken(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $secret = config('app.key');
        if (Str::startsWith($secret, 'base64:')) {
            $secret = base64_decode(substr($secret, 7));
        }

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
