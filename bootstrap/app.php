<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
         api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            $status = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
            $headers = [];

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $headers = $e->getHeaders();
            }

            $message = $e->getMessage() ?: (HttpResponse::$statusTexts[$status] ?? 'Error');

            if ($status >= HttpResponse::HTTP_INTERNAL_SERVER_ERROR && ! config('app.debug')) {
                $message = 'Ha ocurrido un error inesperado.';
            }

            $response = ['message' => $message];

            if (config('app.debug')) {
                $response['exception'] = class_basename($e);
                $response['trace'] = array_slice($e->getTrace(), 0, 5);
            }

            return response()->json($response, $status, $headers);
        });
    })->create();
