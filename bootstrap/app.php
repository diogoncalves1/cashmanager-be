<?php

use App\Http\Middleware\CheckAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix(config('app.api_prefix'))
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => CheckAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is(config('app.api_prefix').'/*') || $request->expectsJson()
        );
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if (! $request->is(config('app.api_prefix').'/*') || ! $response instanceof JsonResponse) {
                return $response;
            }

            $original = $response->getData(true);
            $body = [
                'success' => false,
                'message' => $original['message'] ?? '',
                'data' => null,
            ];
            if (isset($original['errors'])) {
                $body['errors'] = $original['errors'];
            }

            return $response->setData($body);
        });
    })->create();
