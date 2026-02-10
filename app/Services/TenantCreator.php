<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantCreator
{
    /**
     * Create a new tenant database and run migrations.
     *
     * @param string $companyName
     * @param string $subdomain
     * @param string $niche
     * @return \App\Models\Tenant
     */
    public function createTenant(string $companyName, string $subdomain, string $niche): Tenant
    {
        // 1. Generate DB Name
        $dbName = 'tenant_' . Str::slug($subdomain, '_') . '_' . time();
        $dbUser = null; // Can generate or use shared
        $dbPass = null; // Can generate or use shared

        // 2. Create actual database (MySQL specific syntax)
        DB::connection('landlord')->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // 3. Register Tenant in Master DB
        $tenant = Tenant::create([
            'nome_empresa' => $companyName,
            'subdominio' => $subdomain,
            'db_nome' => $dbName,
            'db_usuario' => $dbUser,
            'db_senha' => $dbPass,
            'nicho' => $niche,
            'status' => true,
        ]);

        // 4. Run Migrations on the new DB
        $this->migrateTenant($tenant);

        return $tenant;
    }

    /**
     * Run migrations for a specific tenant.
     *
     * @param \App\Models\Tenant $tenant
     * @return void
     */
    protected function migrateTenant(Tenant $tenant)
    {
        // Temporarily configure the 'tenant' connection
        Config::set('database.connections.tenant.database', $tenant->db_nome);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Run migrations with the 'tenant' connection and correct path
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant', // Separate migrations for tenants
            '--force' => true,
        ]);
    }
}
