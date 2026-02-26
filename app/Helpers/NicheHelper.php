<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

if (!function_exists('niche_translate')) {
    /**
     * Traduz uma string baseada no nicho atual e no idioma do sistema.
     */
    function niche_translate($string, $company = null)
    {
        $entities = niche('entities', null, $company);
        $entity = niche('entity', null, $company);
        $inventory = niche('inventory_items', null, $company);
        $currentNiche = get_current_niche($company);

        $search = [
            'Clientes & Veículos',
            'Clientes & Veiculos',
            'Customers & Vehicles',
            'Customers & Entities',
            'Clientes & Entidades',
            'Customers',
            'Entities',
            'Entidades',
            'Veículos',
            'Veiculos',
            'Vehicles',
            'Veículo',
            'Veiculo',
            'Vehicle',
            'Itens/Peças',
            'Peças',
            'Pecas',
            'Items/Parts',
            'Parts',
            'Histórico do veículo',
            'Vehicle history',
            'Dossiê do Veículo',
            'Vehicle dossier',
            'Oficina',
            'Workshop',
            'Sua oficina',
            'da oficina',
            'Logotipo da Oficina',
            'Contratos de Manutenção',
            'Maintenance Contracts',
            'Serviço',
            'Serviços'
        ];

        $workshopReplacement = ($currentNiche === 'construction') ? 'Canteiro' : __('Empresa');

        $replace = [
            __('Clientes') . ' & ' . $entities,
            __('Clientes') . ' & ' . $entities,
            __('Customers') . ' & ' . $entities,
            __('Customers') . ' & ' . $entities,
            __('Clientes') . ' & ' . $entities,
            __('Clientes'),
            $entities,
            $entities,
            $entities,
            $entities,
            $entities,
            $entity,
            $entity,
            $entity,
            $inventory,
            $inventory,
            $inventory,
            $inventory,
            $inventory,
            __('Histórico da') . ' ' . strtolower($entity),
            __('Vehicle history'),
            __('Dossiê da') . ' ' . $entity,
            __('Vehicle dossier'),
            $workshopReplacement,
            __('Business'),
            'Sua ' . strtolower($workshopReplacement),
            'da ' . strtolower($workshopReplacement),
            'Logotipo da ' . $workshopReplacement,
            __('Contratos de') . ' ' . $entities,
            'Maintenance Contracts',
            in_array($currentNiche, ['pet', 'beauty_clinic']) ? 'Atendimento' : 'Serviço',
            in_array($currentNiche, ['pet', 'beauty_clinic']) ? 'Atendimentos' : 'Serviços'
        ];

        $string = str_ireplace($search, $replace, $string);

        // placeholders
        $urlSlug = strtolower(niche('url_slug', 'vehicle', $company));
        $entitiesSlug = strtolower(niche('url_entities_slug', 'vehicles', $company));
        $clientSlug = strtolower(niche('url_client_slug', 'client', $company));
        $clientsSlug = strtolower(niche('url_clients_slug', 'clients', $company));

        $string = str_replace('{niche_slug}', $urlSlug, $string);
        $string = str_replace('{niche_entities_slug}', $entitiesSlug, $string);
        $string = str_replace('{niche_client_slug}', $clientSlug, $string);
        $string = str_replace('{niche_clients_slug}', $clientsSlug, $string);
        $string = str_replace('{maintenance_contracts_slug}', ($currentNiche === 'pet' ? 'planos' : 'contratos'), $string);

        return $string;
    }
}

if (!function_exists('get_current_niche')) {
    /**
     * Get the current niche name, ensuring a valid fallback.
     */
    function get_current_niche($company = null)
    {
        // 1. Try from a provided company object, if it's not empty
        if ($company && !empty(trim($company->niche))) {
            return $company->niche;
        }

        // 2. Try from the authenticated user
        if (Auth::check()) {
            $user = Auth::user();
            // Try user preference first
            if (!empty(trim($user->niche))) {
                return $user->niche;
            }
            // Fallback to user's company niche
            if ($user->company && !empty(trim($user->company->niche))) {
                return $user->company->niche;
            }
        }

        // 3. Fallback to the .env configuration
        $envNiche = Config::get('niche.current');
        if (!empty(trim($envNiche))) {
            return $envNiche;
        }

        // 4. Final, absolute fallback if everything else fails
        return 'automotive';
    }
}

if (!function_exists('niche')) {
    /**
     * Get the current niche configuration.
     *
     * @param string|null $key
     * @param mixed $default
     * @param object|null $company
     * @return mixed
     */
    function niche($key = null, $default = null, $company = null)
    {
        $currentNiche = get_current_niche($company);

        if (is_null($key)) {
            return $currentNiche;
        }

        // Try to get from root niche config first (for nested keys like icons.entity)
        $value = Config::get("niche.niches.{$currentNiche}.{$key}");

        if (!is_null($value)) {
            return $value;
        }

        // Fallback to labels for flat keys
        return Config::get("niche.niches.{$currentNiche}.labels.{$key}", $default);
    }
}

if (!function_exists('niche_icon')) {
    /**
     * Get the current niche icon.
     *
     * @param string $key
     * @param mixed $default
     * @param object|null $company
     * @return mixed
     */
    function niche_icon($key, $default = null, $company = null)
    {
        $currentNiche = get_current_niche($company);
        $icon = Config::get("niche.niches.{$currentNiche}.icons.{$key}", $default);

        // Ensure icon has the prefix if it's from the old format
        if ($icon && !str_starts_with($icon, 'tabler-') && !str_starts_with($icon, 'ti')) {
            $icon = 'tabler-' . str_replace('ti-', '', $icon);
        } elseif ($icon && str_starts_with($icon, 'ti-')) {
            $icon = 'tabler-' . substr($icon, 3);
        }

        return $icon;
    }
}

if (!function_exists('niche_config')) {
    /**
     * Get a specific configuration key for the current niche.
     *
     * @param string $key
     * @param mixed $default
     * @param object|null $company
     * @return mixed
     */
    function niche_config($key, $default = null, $company = null)
    {
        $currentNiche = get_current_niche($company);
        return Config::get("niche.niches.{$currentNiche}.{$key}", $default);
    }
}
