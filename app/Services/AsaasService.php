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
        $this->apiKey = config('services.asaas.key') ?: env('ASAAS_API_KEY');
        $this->baseUrl = config('services.asaas.url') ?: 'https://sandbox.asaas.com/api/v3';
    }

    public function getOrCreateCustomer($user)
    {
        if (empty($this->apiKey)) {
            throw new \Exception("Chave de API do Asaas não configurada.");
        }

        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->get($this->baseUrl . '/customers', [
                'cpfCnpj' => preg_replace('/\D/', '', $user->cpf_cnpj)
            ]);

        if ($response->successful() && !empty($response->json('data'))) {
            return $response->json('data')[0]['id'];
        }

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

        throw new \Exception("Erro ao criar cliente no Asaas.");
    }

    /**
     * Cria cobrança/assinatura com suporte a Cartão Direto
     */
    public function createSubscription($customerId, $method, $amount, $description, $cardData = null, $user = null)
    {
        $payload = [
            'customer' => $customerId,
            'billingType' => strtoupper($method),
            'value' => $amount,
            'nextDueDate' => now()->addDays(1)->toDateString(),
            'cycle' => 'MONTHLY',
            'description' => $description
        ];

        if ($method === 'credit_card' && $cardData) {
            $payload['creditCard'] = $cardData;
            $payload['creditCardHolderInfo'] = $this->formatHolderInfo($user);
            $payload['remoteIp'] = request()->ip();
        }

        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post($this->baseUrl . '/subscriptions', $payload);

        return $response->json();
    }

    public function createPayment($customerId, $method, $amount, $description, $cardData = null, $user = null)
    {
        $payload = [
            'customer' => $customerId,
            'billingType' => strtoupper($method),
            'value' => $amount,
            'dueDate' => now()->toDateString(),
            'description' => $description
        ];

        if ($method === 'credit_card' && $cardData) {
            $payload['creditCard'] = $cardData;
            $payload['creditCardHolderInfo'] = $this->formatHolderInfo($user);
            $payload['remoteIp'] = request()->ip();
        }

        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post($this->baseUrl . '/payments', $payload);

        return $response->json();
    }

    protected function formatHolderInfo($user)
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'cpfCnpj' => preg_replace('/\D/', '', $user->cpf_cnpj),
            'postalCode' => preg_replace('/\D/', '', $user->zip_code),
            'addressNumber' => 'SN',
            'phone' => preg_replace('/\D/', '', $user->contact_number),
        ];
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
