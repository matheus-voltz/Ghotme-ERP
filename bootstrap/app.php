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
    ->booted(function () {
        // Fallback para evitar erro se as chaves do Reverb não existirem (ex: Deploy na Hostinger sem .env pronto)
        if (config('broadcasting.default') === 'reverb' && !env('REVERB_APP_KEY')) {
            config(['broadcasting.default' => 'log']);
        }
        
        if (config('broadcasting.default') === 'log' && env('REVERB_APP_KEY')) {
            config(['broadcasting.default' => 'reverb']);
        }
    })
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
            if ($e instanceof \Illuminate\Database\QueryException || $e instanceof \PDOException) {
                try {
                    \App\Models\SystemError::create([
                        'user_id' => auth()->id() ?? null,
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'error_type' => get_class($e),
                        'message' => $e->getMessage(),
                        'stack_trace' => $e->getTraceAsString(),
                        'request_data' => request()->all(),
                    ]);

                    // Notifica o MASTER sobre o erro crítico
                    $master = \App\Models\User::where('is_master', true)->first();
                    if ($master) {
                        $master->notify(new \App\Notifications\SystemAlertNotification(
                            "🚨 Erro Crítico de SQL!",
                            "Um erro de banco de dados ocorreu na URL: " . request()->path()
                        ));
                    }
                } catch (\Exception $logException) {
                    // Se falhar ao salvar no banco, apenas segue o fluxo padrão (logs de arquivo)
                }
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
