<?php
 
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
 
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: '',               // endpoint: /orders, bukan /api/orders
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Selalu balas JSON, tidak peduli header Accept
        $exceptions->shouldRenderJsonWhen(fn () => true);
 
        // id non-angka atau URL tidak dikenal -> 404 JSON
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json(['message' => 'Sumber daya tidak ditemukan'], 404);
        });
 
        $exceptions->render(function (MethodNotAllowedHttpException $e) {
            return response()->json(['message' => 'Method tidak diizinkan'], 405);
        });
    })->create();
