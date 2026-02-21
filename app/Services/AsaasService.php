<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        // Tenta ler das configurações do Laravel ou direto do ENV
        $this->apiKey = config('services.asaas.key') ?: env('ASAAS_API_KEY');
        
        // Se ainda estiver vazio, tenta ler via getenv (alguns servidores requerem)
        if (empty($this->apiKey)) {
            $this->apiKey = getenv('ASAAS_API_KEY');
        }

        $this->baseUrl = config('services.asaas.url') ?: 'https://sandbox.asaas.com/api/v3';

        if (empty($this->apiKey)) {
            Log::error('ERRO CRÍTICO: ASAAS_API_KEY não encontrada em nenhum lugar (config, env ou getenv).');
        }
    }

    public function getOrCreateCustomer($user)
    {
        if (empty($this->apiKey)) {
            throw new \Exception("Chave de API do Asaas não configurada. Verifique seu arquivo .env");
        }

        // 1. Tentar buscar por CPF/CNPJ
        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->get($this->baseUrl . '/customers', [
                'cpfCnpj' => preg_replace('/\D/', '', $user->cpf_cnpj)
            ]);

        if ($response->successful() && !empty($response->json('data'))) {
            return $response->json('data')[0]['id'];
        }

        // 2. Se não achar, criar novo
        $createResponse = Http::withHeaders(['access_token' => $this->apiKey])
            ->post($this->baseUrl . '/customers', [
                'name' => $user->name,
                'cpfCnpj' => preg_replace('/\D/', '', $user->cpf_cnpj),
                'email' => $user->email,
                'mobilePhone' => preg_replace('/\D/', '', $user->contact_number),
                'externalReference' => (string) $user->id,
            ]);

        if ($createResponse->successful()) {
            return $createResponse->json('id');
        }

        $errorMsg = $createResponse->json('errors')[0]['description'] ?? 'Erro desconhecido na API do Asaas';
        throw new \Exception("Erro ao criar cliente no Asaas: " . $errorMsg);
    }

    public function createSubscription($customerId, $method, $amount, $description)
    {
        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post($this->baseUrl . '/subscriptions', [
                'customer' => $customerId,
                'billingType' => strtoupper($method),
                'value' => $amount,
                'nextDueDate' => now()->addDays(1)->toDateString(),
                'cycle' => 'MONTHLY',
                'description' => $description
            ]);

        return $response->json();
    }

    public function createPayment($customerId, $method, $amount, $description)
    {
        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post($this->baseUrl . '/payments', [
                'customer' => $customerId,
                'billingType' => strtoupper($method),
                'value' => $amount,
                'dueDate' => now()->toDateString(),
                'description' => $description
            ]);

        return $response->json();
    }

    public function getSubscriptionPayments($subscriptionId)
    {
        return Http::withHeaders(['access_token' => $this->apiKey])
            ->get($this->baseUrl . "/subscriptions/{$subscriptionId}/payments")
            ->json();
    }

    public function getPixData($paymentId)
    {
        return Http::withHeaders(['access_token' => $this->apiKey])
            ->get($this->baseUrl . "/payments/{$paymentId}/pixQrCode")
            ->json();
    }
}
