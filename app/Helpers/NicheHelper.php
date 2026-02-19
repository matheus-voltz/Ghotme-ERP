<?php

use Illuminate\Support\Facades\Config;

if (!function_exists('niche_translate')) {
    /**
     * Traduz uma string baseada no nicho atual.
     */
    function niche_translate($string)
    {
        $search = [
            'Clientes & Veículos', 'Clientes & Veiculos',
            'Veículos', 'Veiculos', 'Veículo', 'Veiculo',
            'veículos', 'veiculos', 'veículo', 'veiculo',
            'Itens/Peças', 'Peças', 'Pecas',
            'Histórico do veículo', 'Historico do veiculo',
            'Dossiê do Veículo', 'Dossie do Veiculo'
        ];

        $replace = [
            'Clientes & ' . niche('entities'), 'Clientes & ' . niche('entities'),
            niche('entities'), niche('entities'), niche('entity'), niche('entity'),
            strtolower(niche('entities')), strtolower(niche('entities')), strtolower(niche('entity')), strtolower(niche('entity')),
            niche('inventory_items'), niche('inventory_items'), niche('inventory_items'),
            'Histórico do ' . strtolower(niche('entity')), 'Histórico do ' . strtolower(niche('entity')),
            'Dossiê do ' . niche('entity'), 'Dossiê do ' . niche('entity')
        ];

        return str_replace($search, $replace, $string);
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
