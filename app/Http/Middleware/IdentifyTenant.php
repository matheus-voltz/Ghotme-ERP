<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        // Se for o domínio principal ou 'www', usar tenant padrão ou landlord
        // Caso não seja um tenant válido, redirecionar ou mostrar erro
        if ($subdomain === 'www' || $subdomain === 'localhost') {
            // Pode ser o ambiente de desenvolvimento sem subdomínio
            // Ou o site institucional
            // Para testes locais, podemos forçar um tenant se quiser
            // return $next($request);
            // OU procurar um tenant 'default'
        }

        $tenant = Tenant::where('subdominio', $subdomain)->first();

        if ($tenant && $tenant->status) {
            $this->tenantManager->setTenant($tenant);
        } else {
            // Opcional: Redirecionar para página de erro ou cadastro
            // abort(404, 'Tenant not found');
            // Por enquanto, apenas logar ou deixar passar se for rota pública
        }

        return $next($request);
    }
}
