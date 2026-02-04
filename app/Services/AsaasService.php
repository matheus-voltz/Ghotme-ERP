<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('ASAAS_API_KEY', '$aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmQxYmU4NGEwLTQ0NTAtNDYzZS1iN2FkLTYxOTEzMTI0YTE3Zjo6JGFhY2hfYjk3NDcyMjgtZjg3OC00YmU1LTllN2MtNDUzMjAxYjQ2MTli');
        $this->baseUrl = env('ASAAS_ENVIRONMENT', 'sandbox') === 'production' 
            ? 'https://www.asaas.com/api/v3' 
            : 'https://sandbox.asaas.com/api/v3';
    }

    /**
     * Busca ou cria um cliente no Asaas.
     */
    public function getOrCreateCustomer($user)
    {
        $cleanCpfCnpj = preg_replace('/\D/', '', $user->cpf_cnpj);

        // Tenta buscar por e-mail primeiro
        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->get("{$this->baseUrl}/customers", ['email' => $user->email]);

        $data = $response->json();

        if (!empty($data['data'])) {
            $customerId = $data['data'][0]['id'];
            
            // Atualiza o cliente existente para garantir que ele tenha o CPF/CNPJ
            Http::withHeaders(['access_token' => $this->apiKey])
                ->post("{$this->baseUrl}/customers/{$customerId}", [
                    'cpfCnpj' => $cleanCpfCnpj
                ]);

            return $customerId;
        }

        // Se não existir, cria
        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post("{$this->baseUrl}/customers", [
                'name' => $user->name,
                'email' => $user->email,
                'cpfCnpj' => $cleanCpfCnpj,
                'externalReference' => $user->id,
            ]);

        return $response->json()['id'] ?? null;
    }

    /**
     * Cria uma cobrança única (Anual).
     */
    public function createPayment($customerId, $method, $amount, $description)
    {
        $billingType = [
            'pix' => 'PIX',
            'boleto' => 'BOLETO',
            'credit_card' => 'CREDIT_CARD'
        ][$method] ?? 'PIX';

        $payload = [
            'customer' => $customerId,
            'billingType' => $billingType,
            'dueDate' => now()->addDays(3)->format('Y-m-d'),
            'value' => $amount,
            'description' => $description,
        ];

        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post("{$this->baseUrl}/payments", $payload);

        return $response->json();
    }

    /**
     * Cria uma assinatura (Mensal).
     */
    public function createSubscription($customerId, $method, $amount, $description)
    {
        $billingType = [
            'pix' => 'PIX',
            'boleto' => 'BOLETO',
            'credit_card' => 'CREDIT_CARD'
        ][$method] ?? 'PIX';

        $payload = [
            'customer' => $customerId,
            'billingType' => $billingType,
            'value' => $amount,
            'nextDueDate' => now()->addDays(1)->format('Y-m-d'), // Primeira cobrança amanhã ou hoje? Geralmente amanhã.
            'cycle' => 'MONTHLY',
            'description' => $description,
        ];

        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post("{$this->baseUrl}/subscriptions", $payload);

        return $response->json();
    }

    /**
     * Obtém o QR Code/Copia e Cola do PIX.
     */
    public function getPixData($paymentId)
    {
        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->get("{$this->baseUrl}/payments/{$paymentId}/pixQrCode");

        return $response->json();
    }

    /**
     * Obtém os pagamentos de uma assinatura.
     */
    public function getSubscriptionPayments($subscriptionId)
    {
        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->get("{$this->baseUrl}/subscriptions/{$subscriptionId}/payments");

        return $response->json();
    }
}
