<?php

use Illuminate\Support\Facades\Config;

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
