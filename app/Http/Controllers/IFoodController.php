<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Jobs\ProcessIFoodOrderJob;

class IFoodController extends Controller
{
    /**
     * Lida com os webhooks recebidos do iFood.
     * 
     * O iFood exige uma resposta rápida (202 Accepted) em até 2 segundos.
     * Portanto, colocamos o processamento pesado em um Job.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-iFood-Signature');

        // Log inicial para depuração (Remover em produção se houver muito volume)
        Log::info('iFood Webhook recebido', ['payload' => $payload]);

        // 1. Validar Assinatura (Opcional por enquanto, até configurar as chaves no .env)
        if (!$this->validateSignature($request, $signature)) {
            Log::warning('iFood Webhook: Assinatura inválida detectada.');
            // return response()->json(['error' => 'Invalid signature'], 403);
        }

        // 2. Extrair dados básicos
        $merchantId = $payload['merchantId'] ?? null;
        $orderId = $payload['orderId'] ?? null;
        $eventType = $payload['fullCode'] ?? null; // Ex: PLACED, CONFIRMED, etc.

        if (!$merchantId || !$orderId) {
            return response()->json(['message' => 'Missing required data'], 400);
        }

        // 3. Buscar Empresa e Validar Nicho
        $company = Company::where('niche', 'food_service')
                         ->where('ifood_merchant_id', $merchantId) 
                         ->first();

        if (!$company) {
            Log::warning("iFood Webhook: Recebido evento para merchant {$merchantId}, mas nenhuma empresa food_service correspondente foi encontrada.");
            return response()->json(['message' => 'Merchant not found or not food_service'], 404);
        }

        // 4. Despachar para Job Processar
        // Passamos apenas o necessário para o Job para ser rápido
        ProcessIFoodOrderJob::dispatch($company->id, $payload);

        // 5. Responder Imediatamente ao iFood
        return response()->json(['message' => 'Webhook received and processing'], 202);
    }

    /**
     * Valida se a requisição realmente veio do iFood.
     */
    private function validateSignature(Request $request, $signature)
    {
        $secret = config('services.ifood.client_secret');
        if (!$secret || !$signature) return false;

        $computedSignature = hash_hmac('sha256', $request->getContent(), $secret);
        return hash_equals($computedSignature, $signature);
    }
}
