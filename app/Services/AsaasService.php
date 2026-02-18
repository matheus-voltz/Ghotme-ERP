<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AsaasService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        // Pega as chaves do .env ou config (Adicionar no seu .env ASAAS_API_KEY)
        $this->apiKey = config('services.asaas.key', env('ASAAS_API_KEY'));
        $this->baseUrl = config('services.asaas.url', 'https://www.asaas.com/api/v3');
    }

    /**
     * Gera um pagamento PIX dinâmico
     */
    public function generatePix($amount, $customerName, $customerCpfCnpj, $description, $externalReference)
    {
        // 1. Criar a cobrança no Asaas
        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post($this->baseUrl . '/payments', [
                'billingType' => 'PIX',
                'value' => $amount,
                'description' => $description,
                'externalReference' => $externalReference,
                'dueDate' => date('Y-m-d'), // Vencimento hoje
                // Idealmente você criaria o cliente no Asaas antes e passaria o ID aqui
                'customer' => $this->getOrCreateCustomer($customerName, $customerCpfCnpj),
            ]);

        if (!$response->successful()) return null;

        $paymentId = $response->json('id');

        // 2. Buscar o QR Code do pagamento gerado
        $pixResponse = Http::withHeaders(['access_token' => $this->apiKey])
            ->get($this->baseUrl . "/payments/{$paymentId}/pixQrCode");

        return $pixResponse->json();
    }

    private function getOrCreateCustomer($name, $cpfCnpj)
    {
        // Lógica simplificada: busca por CPF/CNPJ ou cria um novo
        // Aqui deve ser aprimorado para produção
        return 'cus_000005789456'; // Exemplo de ID fixo para teste
    }
}
