<?php

use Illuminate\Support\Facades\Config;

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
            'Clientes & Veículos', 'Clientes & Veiculos', 'Customers & Vehicles',
            'Veículos', 'Veiculos', 'Vehicles',
            'Veículo', 'Veiculo', 'Vehicle',
            'Itens/Peças', 'Peças', 'Pecas', 'Items/Parts', 'Parts',
            'Histórico do veículo', 'Vehicle history',
            'Dossiê do Veículo', 'Vehicle dossier',
            'Oficina', 'Workshop', 'Sua oficina',
            'do veículo', 'da oficina'
        ];

        $workshopReplacement = ($currentNiche === 'construction') ? 'Canteiro' : __('Empresa');

        $replace = [
            __('Clientes') . ' & ' . $entities, __('Clientes') . ' & ' . $entities, __('Customers') . ' & ' . $entities,
            $entities, $entities, $entities,
            $entity, $entity, $entity,
            $inventory, $inventory, $inventory, $inventory, $inventory,
            __('Histórico da') . ' ' . strtolower($entity), __('Vehicle history'),
            __('Dossiê da') . ' ' . $entity, __('Vehicle dossier'),
            $workshopReplacement, __('Business'), 'Sua ' . strtolower($workshopReplacement),
            'da ' . strtolower($entity), 'da ' . strtolower($workshopReplacement)
        ];

        return str_ireplace($search, $replace, $string);
    }
}

if (!function_exists('get_current_niche')) {
    /**
     * Get the current niche name.
     */
    function get_current_niche($company = null)
    {
        if ($company && !empty($company->niche)) {
            return $company->niche;
        }

        if (auth()->check() && !empty(auth()->user()->niche)) {
            return auth()->user()->niche;
        }

        return Config::get('niche.current', 'automotive');
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
