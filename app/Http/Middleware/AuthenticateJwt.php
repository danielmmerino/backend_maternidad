<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use App\Models\UsuarioSesion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateJwt
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = $request->header('Authorization');

        if (! $authorization || ! Str::startsWith($authorization, 'Bearer ')) {
            return $this->unauthorizedResponse('Token de autorización no proporcionado.');
        }

        $token = trim(Str::after($authorization, 'Bearer '));
        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            return $this->unauthorizedResponse('Token de autorización inválido.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $segments;

        $payloadJson = $this->base64UrlDecode($encodedPayload);
        if ($payloadJson === false) {
            return $this->unauthorizedResponse('No se pudo decodificar el token.');
        }

        $payload = json_decode($payloadJson, true);
        if (! is_array($payload) || ! isset($payload['sub'], $payload['exp'], $payload['jti'])) {
            return $this->unauthorizedResponse('Token de autorización incompleto.');
        }

        $secret = config('app.key');
        if (Str::startsWith($secret, 'base64:')) {
            $secret = base64_decode(substr($secret, 7));
        }

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true)
        );

        if (! hash_equals($expectedSignature, $encodedSignature)) {
            return $this->unauthorizedResponse('La firma del token es inválida.');
        }

        if ($payload['exp'] <= now()->timestamp) {
            return $this->unauthorizedResponse('El token ha expirado.');
        }

        $session = UsuarioSesion::where('jwt_id', $payload['jti'])
            ->whereNull('revoked_at')
            ->where('id_estado', 1)
            ->first();

        if (! $session) {
            return $this->unauthorizedResponse('La sesión del token no es válida.');
        }

        if (! hash_equals($session->token_hash, hash('sha256', $token))) {
            return $this->unauthorizedResponse('El token no coincide con la sesión registrada.');
        }

        if ($session->expires_at && $session->expires_at->isPast()) {
            return $this->unauthorizedResponse('La sesión del token ha expirado.');
        }

        $usuario = Usuario::where('public_id', $payload['sub'])
            ->where('id_estado', 1)
            ->first();

        if (! $usuario) {
            return $this->unauthorizedResponse('El usuario del token no es válido.');
        }

        $request->setUserResolver(fn () => $usuario);
        $request->attributes->set('auth_payload', $payload);

        return $next($request);
    }

    private function unauthorizedResponse(string $message)
    {
        return response()->json(['message' => $message], Response::HTTP_UNAUTHORIZED);
    }

    private function base64UrlDecode(string $value): string|false
    {
        $remainder = strlen($value) % 4;
        if ($remainder) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($value, '-_', '+/'), true);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
