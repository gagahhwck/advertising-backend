<?php

use App\Http\Middleware\ApiKeyMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Permission;
use App\Http\Middleware\Role;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
    api: __DIR__ . '/../routes/api.php',
  )
  ->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\App\Http\Middleware\TrustProxies::class);
    $middleware->alias([
      'auth'        => Authenticate::class,
      'api_key'     => ApiKeyMiddleware::class,
      'permission'  => Permission::class,
      'role'        => Role::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions): void {
    // 403 - Forbidden
    $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'message' => 'Forbidden.'
        ], 403);
      }
    });

    // 404 - Not Found
    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'message' => 'Record not found.'
        ], 404);
      }
    });

    // 405 - Method Not Allowed
    $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'message' => 'Method not allowed.'
        ], 405);
      }
    });

    // 422 - Unprocessable Entity
    $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'message' => 'The given data was invalid.',
          'errors' => $e->errors(),
        ], 422);
      }
    });

    // 500 - Internal Server Error
    $exceptions->render(function (\Exception $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'message' => 'Internal server error.',
          'error' => $e->getMessage(),
          'trace' => $e->getTrace(),
        ], 500);
      }
    });
  })->create();
