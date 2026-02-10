<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Plan;

class PlanoService
{
    /**
     * Get the plan for a specific tenant.
     *
     * @param \App\Models\Tenant $tenant
     * @return \App\Models\Plan|null
     */
    public function getPlanForTenant(Tenant $tenant)
    {
        return $tenant->plan;
    }

    /**
     * Get the employee limit for a tenant.
     *
     * @param \App\Models\Tenant $tenant
     * @return int
     */
    public function getFuncionarioLimit(Tenant $tenant)
    {
        $plan = $this->getPlanForTenant($tenant);

        // If no plan is assigned, return a default safe limit (e.g., 0 or a free tier limit)
        // Adjust this logic based on your business rules.
        return $plan ? $plan->max_funcionarios : 0;
    }
}
