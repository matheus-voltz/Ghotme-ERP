<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LocaleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['middleware' => ['web', 'auth']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'master' => \App\Http\Middleware\MasterAdminMiddleware::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->web(LocaleMiddleware::class);
        $middleware->validateCsrfTokens(except: [
            '/webhook/asaas'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $e) {
            // Ignora erros comuns de validação ou não encontrados para não poluir
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return;
            }

            try {
                $user = auth()->user();
                $userName = $user ? $user->name : 'Visitante';

                \App\Models\SystemError::create([
                    'user_id' => $user->id ?? null,
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'error_type' => get_class($e),
                    'message' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString(),
                    'request_data' => request()->except(['password', 'password_confirmation']),
                ]);

                // Notifica o MASTER sobre qualquer erro crítico
                $master = \App\Models\User::where('is_master', true)->first();
                if ($master && (!$user || !$user->is_master)) {
                    $master->notify(new \App\Notifications\SystemAlertNotification(
                        "🚨 Alerta de Erro: {$userName}",
                        "Erro: " . \Illuminate\Support\Str::limit($e->getMessage(), 60),
                        url('/master/errors')
                    ));
                }
            } catch (\Exception $logException) {
                // Silencioso se falhar o log
            }
        });

        $exceptions->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            // Se for AJAX, retorna JSON limpo
            if ($request->expectsJson()) {
                // Tenta pegar o ID do erro recém criado (gambiarra leve pois o reportable roda antes)
                $errorId = \App\Models\SystemError::latest('id')->first()?->id ?? 'N/A';

                return response()->json([
                    'success' => false,
                    'message' => "Erro no banco de dados (Ref: #{$errorId}). Contate o suporte.",
                    'debug_message' => config('app.debug') ? $e->getMessage() : null // Só mostra o erro real se estiver em debug
                ], 500);
            }
        });
    })->create();
