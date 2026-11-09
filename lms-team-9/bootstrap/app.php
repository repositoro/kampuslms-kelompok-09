<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $statusCode = 500;
                $message = 'Terjadi kesalahan pada server.';
                $errors = [];

                if ($e instanceof AuthenticationException) {
                    $statusCode = 401;
                    $message = 'Unauthenticated.';
                } elseif ($e instanceof AccessDeniedHttpException || $e instanceof AuthorizationException) {
                    $statusCode = 403;
                    $message = 'Anda tidak memiliki akses ke sumber daya ini.';
                } elseif ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                    $statusCode = 404;
                    $message = 'Sumber daya tidak ditemukan.';
                } elseif ($e instanceof ValidationException) {
                    $statusCode = 422;
                    $message = 'Data yang diberikan tidak valid.';
                    $errors = $e->errors();
                } elseif ($e instanceof ThrottleRequestsException) {
                    $statusCode = 429;
                    $message = 'Terlalu banyak percobaan.';
                } elseif ($e instanceof HttpException) {
                    $statusCode = $e->getStatusCode();
                }

                $response = ['message' => $message];
                if (! empty($errors)) {
                    $response['errors'] = $errors;
                }

                return response()->json($response, $statusCode);
            }
        });
    })->create();
