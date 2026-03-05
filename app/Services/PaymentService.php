<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\Company;
use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Obtém a instância do serviço de gateway configurado para a empresa.
     * Retorna null se nenhum gateway estiver ativo ou configurado.
     */
    public function getGatewayService(Company $company): ?object
    {
        $settings = IntegrationSetting::where('company_id', $company->id)->first();

        if (!$settings || !$settings->active_payment_gateway) {
            return null;
        }

        return match ($settings->active_payment_gateway) {
            'asaas' => $this->validateAsaas($settings) ? new AsaasService(
                $settings->asaas_api_key,
                $settings->asaas_environment
            ) : null,
            // Outros gateways podem ser adicionados aqui no futuro
            default => null,
        };
    }

    /**
     * Valida se a integração Asaas está configurada corretamente.
     */
    private function validateAsaas(IntegrationSetting $settings): bool
    {
        return !empty($settings->asaas_api_key) && !empty($settings->asaas_environment);
    }

    /**
     * Gera uma cobrança PIX no gateway se configurado.
     * Se não houver gateway, apenas retorna { gateway: false }.
     */
    public function generatePixCharge(OrdemServico $os): array
    {
        $company = $os->company ?? auth()->user()?->company;
        if (!$company) {
            return ['gateway' => false, 'error' => 'Empresa não encontrada'];
        }

        $gatewayService = $this->getGatewayService($company);

        // Se não há gateway configurado, apenas indica sucesso sem gateway
        if (!$gatewayService) {
            return ['gateway' => false];
        }

        try {
            // Criar/buscar cliente no gateway (assumindo Asaas por enquanto)
            if (!$gatewayService instanceof AsaasService) {
                return ['gateway' => false, 'error' => 'Gateway não suportado'];
            }

            // Obter dados do cliente (usar o user logado para representar a empresa)
            $user = auth()->user();
            if (!$user) {
                return ['gateway' => false, 'error' => 'Usuário não autenticado'];
            }

            $customerId = $gatewayService->getOrCreateCustomer($user);

            // Criar cobrança PIX
            $payment = $gatewayService->createPayment(
                $customerId,
                'PIX',
                $os->total,
                'OS #' . $os->id . ' - ' . ($os->client?->name ?? 'Balcão'),
                null,
                $user
            );

            if (isset($payment['errors'])) {
                Log::error('Erro ao criar cobrança PIX', ['errors' => $payment['errors']]);
                return ['gateway' => true, 'error' => $payment['errors'][0]['description'] ?? 'Erro desconhecido'];
            }

            // Salvar o ID da cobrança na OS
            $os->update([
                'gateway_payment_id' => $payment['id'],
            ]);

            // Buscar dados do PIX (QR Code)
            $pixData = $gatewayService->getPixData($payment['id']);

            return [
                'gateway' => true,
                'payment_id' => $payment['id'],
                'status' => $payment['status'] ?? 'PENDING',
                'qr_code_image' => $pixData['encodedImage'] ?? null,
                'qr_code_text' => $pixData['payload'] ?? null,
                'expiration_date' => $pixData['expirationDate'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao gerar cobrança PIX', ['error' => $e->getMessage()]);
            return ['gateway' => true, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verifica o status de um pagamento PIX no gateway.
     * Retorna true se pagamento foi confirmado.
     */
    public function checkPixStatus(OrdemServico $os): bool
    {
        if (!$os->gateway_payment_id) {
            return false;
        }

        $company = $os->company ?? auth()->user()?->company;
        if (!$company) {
            return false;
        }

        $gatewayService = $this->getGatewayService($company);

        if (!$gatewayService || !$gatewayService instanceof AsaasService) {
            return false;
        }

        try {
            $payment = $gatewayService->getPaymentStatus($os->gateway_payment_id);
            $isPaid = in_array($payment['status'] ?? '', ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH']);

            if ($isPaid) {
                $os->update(['paid_at' => now()]);
            }

            return $isPaid;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status PIX', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
