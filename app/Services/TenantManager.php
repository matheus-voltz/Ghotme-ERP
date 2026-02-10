<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantManager
{
    /**
     * The current tenant instance.
     *
     * @var \App\Models\Tenant|null
     */
    protected $tenant;

    /**
     * Set the current tenant and configure the database connection.
     *
     * @param \App\Models\Tenant $tenant
     * @return void
     */
    public function setTenant(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->configureConnection($tenant);
    }

    /**
     * Get the current tenant.
     *
     * @return \App\Models\Tenant|null
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Configure the tenant database connection dynamically.
     *
     * @param \App\Models\Tenant $tenant
     * @return void
     */
    protected function configureConnection(Tenant $tenant)
    {
        // Set the database name for the 'tenant' connection
        Config::set('database.connections.tenant.database', $tenant->db_nome);

        // Optional: Set specific user/password if provided, otherwise use default from .env
        if ($tenant->db_usuario) {
            Config::set('database.connections.tenant.username', $tenant->db_usuario);
        }
        if ($tenant->db_senha) {
            Config::set('database.connections.tenant.password', $tenant->db_senha);
        }

        // Purge the old connection to force a reconnect with new config
        DB::purge('tenant');

        // Reconnect
        DB::reconnect('tenant');

        // Verify connection (optional but good for debugging)
        try {
            DB::connection('tenant')->getPdo();
        } catch (\Exception $e) {
            throw new \RuntimeException("Could not connect to tenant database: " . $e->getMessage());
        }

        // Set the niche configuration for this tenant
        // Assuming your NicheHelper uses config/niche.php which reads from env or config
        // Here we can override it dynamically
        Config::set('niche.current', $tenant->nicho);

        // Update the default connection to be 'tenant' so Models use it automatically
        Config::set('database.default', 'tenant');
    }
}
