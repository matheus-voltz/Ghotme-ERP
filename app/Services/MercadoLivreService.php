<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use App\Models\MarketplacePublication;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MercadoLivreService
{
    protected $settings;
    protected $baseUrl = 'https://api.mercadolibre.com';
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
        $this->settings = IntegrationSetting::where('company_id', $companyId)->first();
    }

    /**
     * Retorna a URL para o usuário autorizar o app no Mercado Livre
     */
    public function getAuthUrl($redirectUri)
    {
        if (!$this->settings->meli_client_id) {
            throw new \Exception("Mercado Livre Client ID não configurado para esta empresa.");
        }

        return "https://auth.mercadolivre.com.br/authorization?response_type=code&client_id={$this->settings->meli_client_id}&redirect_uri={$redirectUri}";
    }

    /**
     * Troca o código de autorização pelo Access Token
     */
    public function handleCallback($code, $redirectUri)
    {
        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type' => 'authorization_code',
            'client_id' => $this->settings->meli_client_id,
            'client_secret' => $this->settings->meli_client_secret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->updateTokens($data);
            return $data;
        }

        Log::error("Meli OAuth Error: " . $response->body());
        throw new \Exception("Erro ao autenticar com Mercado Livre.");
    }

    /**
     * Atualiza o token se estiver expirado
     */
    public function refreshToken()
    {
        if (!$this->settings->meli_refresh_token) return false;

        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type' => 'refresh_token',
            'client_id' => $this->settings->meli_client_id,
            'client_secret' => $this->settings->meli_client_secret,
            'refresh_token' => $this->settings->meli_refresh_token,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->updateTokens($data);
            return true;
        }

        return false;
    }

    protected function updateTokens($data)
    {
        $this->settings->update([
            'meli_access_token' => $data['access_token'],
            'meli_refresh_token' => $data['refresh_token'],
            'meli_user_id' => $data['user_id'],
            'meli_token_expires_at' => now()->addSeconds($data['expires_in']),
            'meli_active' => true,
        ]);
    }

    /**
     * Publica um novo anúncio no Mercado Livre
     */
    public function publishItem($publishable, $price, $categoryId, $condition = 'new')
    {
        $this->ensureTokenIsValid();

        $images = $publishable->images->map(function($img) {
            return ['source' => asset('storage/' . $img->path)];
        })->toArray();

        $payload = [
            'title' => substr($publishable->name, 0, 60),
            'category_id' => $categoryId,
            'price' => (float) $price,
            'currency_id' => 'BRL',
            'available_quantity' => $publishable->quantity ?? 1,
            'buying_mode' => 'buy_it_now',
            'listing_type_id' => 'bronze', // Clássico
            'condition' => $condition,
            'description' => ['plain_text' => $publishable->description ?? $publishable->name],
            'pictures' => $images,
        ];

        $response = Http::withToken($this->settings->meli_access_token)
            ->post("{$this->baseUrl}/items", $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            return MarketplacePublication::create([
                'company_id' => $this->companyId,
                'publishable_id' => $publishable->id,
                'publishable_type' => get_class($publishable),
                'platform' => 'mercado_livre',
                'external_id' => $data['id'],
                'external_url' => $data['permalink'],
                'status' => 'active',
                'price' => $price,
            ]);
        }

        Log::error("Meli Publish Error: " . $response->body());
        throw new \Exception("Erro ao publicar no Mercado Livre: " . ($response->json('message') ?? 'Erro desconhecido'));
    }

    /**
     * Sincroniza o estoque com o Mercado Livre
     */
    public function syncStock(MarketplacePublication $publication)
    {
        $this->ensureTokenIsValid();

        $publishable = $publication->publishable;
        if (!$publishable) return;

        $response = Http::withToken($this->settings->meli_access_token)
            ->put("{$this->baseUrl}/items/{$publication->external_id}", [
                'available_quantity' => $publishable->quantity
            ]);

        if ($response->successful()) {
            $publication->update(['last_synced_at' => now()]);
            return true;
        }

        return false;
    }

    protected function ensureTokenIsValid()
    {
        if (Carbon::parse($this->settings->meli_token_expires_at)->isPast()) {
            if (!$this->refreshToken()) {
                throw new \Exception("Conexão com Mercado Livre expirou. Por favor, reconecte.");
            }
        }
    }
}
