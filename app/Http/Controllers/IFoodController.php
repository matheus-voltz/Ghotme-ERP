<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessIFoodOrderJob;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IFoodController extends Controller
{
    public function index()
    {
        return view('content.ifood.orders');
    }

    public function handleWebhook(Request $request)
    {
        $secret = config('services.ifood.webhook_secret');

        if ($secret) {
            $signature = $request->header('X-iFood-Signature');
            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            if (!hash_equals($expected, (string) $signature)) {
                Log::warning('iFood webhook: assinatura inválida rejeitada.');
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        $payload = $request->json()->all();
        $merchantId = $payload['merchantId'] ?? null;

        $company = Company::where('ifood_merchant_id', $merchantId)->first();

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        ProcessIFoodOrderJob::dispatch($company->id, $payload);

        return response()->json(['success' => true]);
    }
}
