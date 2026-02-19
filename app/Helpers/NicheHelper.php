<?php

use Illuminate\Support\Facades\Config;

if (!function_exists('niche_translate')) {
    /**
     * Traduz uma string baseada no nicho atual e no idioma do sistema.
     */
    function niche_translate($string)
    {
        // Se a string contiver os termos base, fazemos a substituição baseada no nicho
        // O str_ireplace ignora maiúsculas/minúsculas
        
        $entities = niche('entities');
        $entity = niche('entity');
        $inventory = niche('inventory_items');

        // Mapeamento de termos que devem ser trocados pelo termo do nicho
        // Buscamos tanto em PT quanto em EN para garantir que a troca ocorra
        $search = [
            'Clientes & Veículos', 'Clientes & Veiculos', 'Customers & Vehicles',
            'Veículos', 'Veiculos', 'Vehicles',
            'Veículo', 'Veiculo', 'Vehicle',
            'Itens/Peças', 'Peças', 'Pecas', 'Items/Parts', 'Parts',
            'Histórico do veículo', 'Vehicle history',
            'Dossiê do Veículo', 'Vehicle dossier',
            'Oficina', 'Workshop', 'Sua oficina'
        ];

        $replace = [
            __('Clientes') . ' & ' . $entities, __('Clientes') . ' & ' . $entities, __('Customers') . ' & ' . $entities,
            $entities, $entities, $entities,
            $entity, $entity, $entity,
            $inventory, $inventory, $inventory, $inventory, $inventory,
            __('Histórico do') . ' ' . strtolower($entity), __('Vehicle history'),
            __('Dossiê do') . ' ' . $entity, __('Vehicle dossier'),
            __('Empresa'), __('Business'), __('Sua empresa')
        ];

        return str_ireplace($search, $replace, $string);
    }
}

if (!function_exists('niche')) {
    /**
     * Get the current niche configuration.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function niche($key = null, $default = null)
    {
        $userNiche = auth()->id() && !empty(auth()->user()->niche) ? auth()->user()->niche : null;
        $defaultNiche = Config::get('niche.current', 'automotive');

        // Check if user's niche exists in config, otherwise fallback to default
        $currentNiche = $userNiche && Config::has("niche.niches.{$userNiche}")
            ? $userNiche
            : $defaultNiche;

        // Mapping 'tech_assistance' to 'electronics' if needed, or just ensure config keys match user niche values
        // For now assuming user niche values match config structure keys

        if (is_null($key)) {
            return $currentNiche;
        }

        return Config::get("niche.niches.{$currentNiche}.labels.{$key}", $default);
    }
}

if (!function_exists('niche_config')) {
    /**
     * Get a specific configuration key for the current niche.
     * Use this for non-label configs like icons or components.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function niche_config($key, $default = null)
    {
        $currentNiche = Config::get('niche.current', 'automotive');
        return Config::get("niche.niches.{$currentNiche}.{$key}", $default);
    }
}
