<?php

namespace App\Services;

use App\Models\Funcionario;
use Illuminate\Validation\ValidationException;

class FuncionarioService
{
    protected $planoService;
    protected $tenantManager;

    public function __construct(PlanoService $planoService, TenantManager $tenantManager)
    {
        $this->planoService = $planoService;
        $this->tenantManager = $tenantManager;
    }

    /**
     * Create a new funcionario for the current tenant.
     *
     * @param array $data
     * @return \App\Models\Funcionario
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createFuncionario(array $data)
    {
        // 1. Get Current Tenant
        $tenant = $this->tenantManager->getTenant();

        if (!$tenant) {
            // Fallback or error if no tenant context
            // In a real app middleware should handle this, but safe guard here
            throw ValidationException::withMessages(['tenant' => 'Erro: Tenant não identificado.']);
        }

        // 2. Count Active Funcionarios (using current tenant connection)
        $count = Funcionario::where('status', true)->count();

        // 3. Get Plan Limit
        $limit = $this->planoService->getFuncionarioLimit($tenant);

        // 4. Check Limit
        if ($count >= $limit) {
            throw ValidationException::withMessages([
                'limit' => 'Limite de funcionários do seu plano atingido. Faça upgrade para continuar.'
            ]);
        }

        // 5. Create Funcionario
        return Funcionario::create($data);
    }
}
