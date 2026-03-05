<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApiPixPaymentController extends Controller
{
    /**
     * Gera cobrança PIX via Asaas e retorna QR Code + copia-e-cola.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'customer_name' => 'nullable|string|max:255',
        ]);

        try {
            $asaas = new AsaasService();
            $user = Auth::user();

            // Criar/buscar cliente no Asaas (usa dados da empresa)
            $customerId = $asaas->getOrCreateCustomer($user);

            // Criar cobrança PIX
            $payment = $asaas->createPayment(
                $customerId,
                'PIX',
                $request->amount,
                $request->description,
                null,
                $user
            );

            if (isset($payment['errors'])) {
                return response()->json([
                    'success' => false,
                    'message' => $payment['errors'][0]['description'] ?? 'Erro ao gerar cobrança PIX.',
                ], 422);
            }

            // Buscar QR Code da cobrança
            $pixData = $asaas->getPixData($payment['id']);

            return response()->json([
                'success' => true,
                'payment_id' => $payment['id'],
                'status' => $payment['status'],
                'qr_code_image' => $pixData['encodedImage'] ?? null,  // Base64 da imagem
                'qr_code_text' => $pixData['payload'] ?? null,        // Copia-e-cola
                'expiration_date' => $pixData['expirationDate'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro PIX: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verifica status do pagamento PIX (polling).
     */
    public function status($paymentId)
    {
        try {
            $asaas = new AsaasService();
            $payment = $asaas->getPaymentStatus($paymentId);

            $isPaid = in_array($payment['status'] ?? '', ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH']);

            return response()->json([
                'success' => true,
                'payment_id' => $paymentId,
                'status' => $payment['status'] ?? 'UNKNOWN',
                'is_paid' => $isPaid,
                'value' => $payment['value'] ?? 0,
                'net_value' => $payment['netValue'] ?? 0,
                'paid_at' => $payment['confirmedDate'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
