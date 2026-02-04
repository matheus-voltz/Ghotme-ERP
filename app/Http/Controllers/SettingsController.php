<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\BillingHistory;
use App\Services\AsaasService;

class SettingsController extends Controller
{
    protected $asaas;

    public function __construct(AsaasService $asaas)
    {
        $this->asaas = $asaas;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Trial logic
        $trialDaysLeft = 0;
        if ($user->trial_ends_at) {
            $trialDaysLeft = max(0, now()->diffInDays($user->trial_ends_at, false));
        } else {
            // Set trial if not set (first time accessing settings maybe?)
            $user->trial_ends_at = $user->created_at->addDays(30);
            $user->save();
            $trialDaysLeft = max(0, now()->diffInDays($user->trial_ends_at, false));
        }

        $billingHistory = BillingHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Current Plan Details
        $planDetails = [
            'name' => 'Padrão',
            'price' => '149,00',
            'period' => 'Mês',
            'description' => 'O plano ideal para o crescimento da sua oficina.'
        ];

        if ($user->plan === 'enterprise') {
            $planDetails = [
                'name' => 'Enterprise',
                'price' => $user->plan_type === 'yearly' ? '2.790,00' : '279,00',
                'period' => $user->plan_type === 'yearly' ? 'Ano' : 'Mês',
                'description' => 'Solução completa para grandes operações e frotas.'
            ];
        } elseif ($user->plan === 'padrao') {
            $planDetails = [
                'name' => 'Padrão',
                'price' => $user->plan_type === 'yearly' ? '1.490,00' : '149,00',
                'period' => $user->plan_type === 'yearly' ? 'Ano' : 'Mês',
                'description' => 'O plano ideal para o crescimento da sua oficina.'
            ];
        } elseif ($user->plan === 'free') {
            $planDetails = [
                'name' => 'Teste Grátis',
                'price' => '0,00',
                'period' => 'Mês',
                'description' => 'Você está aproveitando os 30 dias de avaliação gratuita.'
            ];
        }

        return view('content.pages.billing.pages-account-settings-billing', compact('user', 'trialDaysLeft', 'billingHistory', 'planDetails'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        // Clean formatting
        $cleanCpfCnpj = preg_replace('/\D/', '', $request->cpf_cnpj);
        $cleanZip = preg_replace('/\D/', '', $request->zipCode);

        $user->update([
            'company' => $request->companyName,
            'email' => $request->billingEmail,
            'cpf_cnpj' => $cleanCpfCnpj,
            'contact_number' => $request->mobileNumber,
            'country' => $request->country,
            'billing_address' => $request->billingAddress,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $cleanZip,
        ]);

        return response()->json(['success' => true, 'message' => 'Perfil de cobrança atualizado!']);
    }

    public function selectPlan(Request $request)
    {
        $user = auth()->user();
        $plan = $request->plan; // padrao, enterprise
        $type = $request->type; // monthly, yearly
        $method = $request->method ?? 'pix'; // default method for initial generation

        $plans = [
            'padrao' => ['monthly' => 149.00, 'yearly' => 1490.00],
            'enterprise' => ['monthly' => 279.00, 'yearly' => 2790.00],
        ];

        if (!isset($plans[$plan])) {
            return response()->json(['success' => false, 'message' => 'Plano inválido.']);
        }

        $amount = $plans[$plan][$type];
        $description = "Plano " . ucfirst($plan) . " - Ghotme (" . ($type === 'monthly' ? 'Mensal' : 'Anual') . ")";

        if (!$user->cpf_cnpj) {
            return response()->json(['success' => false, 'message' => 'Por favor, preencha seu CPF ou CNPJ no perfil antes de escolher um plano.']);
        }

        try {
            $customerId = $this->asaas->getOrCreateCustomer($user);
            
            if ($type === 'monthly') {
                // Cria Assinatura
                $result = $this->asaas->createSubscription($customerId, $method, $amount, $description);
            } else {
                // Cria Pagamento Único
                $result = $this->asaas->createPayment($customerId, $method, $amount, $description);
            }

            if (!isset($result['id'])) {
                return response()->json(['success' => false, 'message' => $result['errors'][0]['description'] ?? 'Erro ao processar no Asaas.']);
            }

            // Atualiza o plano do usuário imediatamente
            $user->update([
                'plan' => $plan,
                'plan_type' => $type
            ]);

            // Registra no histórico
            BillingHistory::create([
                'user_id' => $user->id,
                'plan_name' => ucfirst($plan) . " (" . ($type === 'monthly' ? 'Mensal' : 'Anual') . ")",
                'amount' => $amount,
                'payment_method' => ucfirst($method),
                'status' => 'pending',
            ]);

            $invoiceUrl = $result['invoiceUrl'] ?? null;

            // If it's a subscription, we might need to fetch the first payment's URL
            if ($type === 'monthly' && !$invoiceUrl) {
                $payments = $this->asaas->getSubscriptionPayments($result['id']);
                if (!empty($payments['data'])) {
                    $invoiceUrl = $payments['data'][0]['invoiceUrl'] ?? null;
                }
            }

            $responseData = [
                'success' => true,
                'message' => 'Plano selecionado com sucesso! Aguardando pagamento.',
                'invoice_url' => $invoiceUrl,
            ];

            if ($method === 'pix') {
                if ($type === 'monthly') {
                    $responseData['redirect_url'] = $invoiceUrl;
                } else {
                    $pixData = $this->asaas->getPixData($result['id']);
                    $responseData['pix_code'] = $pixData['payload'] ?? null;
                    $responseData['pix_qr'] = $pixData['encodedImage'] ?? null;
                }
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function generatePayment(Request $request)
    {
        $user = auth()->user();
        $method = $request->method; // pix, boleto, credit_card
        
        // Define prices mapping
        $planPrices = [
            'free' => 149.00, // Default to standard if on trial
            'padrao' => 149.00,
            'enterprise' => 279.00
        ];

        $amount = $planPrices[$user->plan] ?? 149.00;
        $description = "Assinatura Plano " . ucfirst($user->plan === 'free' ? 'Padrão' : $user->plan) . " - Ghotme";

        if (!$user->cpf_cnpj) {
            return response()->json(['success' => false, 'message' => 'Por favor, preencha seu CPF ou CNPJ no perfil antes de gerar a cobrança.']);
        }

        try {
            $customerId = $this->asaas->getOrCreateCustomer($user);
            if (!$customerId) throw new \Exception("Erro ao processar cliente no Asaas.");

            $payment = $this->asaas->createPayment($customerId, $method, $amount, $description);
            
            if (!isset($payment['id'])) {
                return response()->json(['success' => false, 'message' => $payment['errors'][0]['description'] ?? 'Erro no Asaas']);
            }

            // Registra no histórico local como pendente
            BillingHistory::create([
                'user_id' => $user->id,
                'plan_name' => 'Oficina Pro',
                'amount' => $amount,
                'payment_method' => ucfirst($method),
                'status' => 'pending',
            ]);

            $responseData = [
                'success' => true,
                'payment_id' => $payment['id'],
                'amount' => number_format($amount, 2, ',', '.'),
                'invoice_url' => $payment['invoiceUrl'] ?? null,
                'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
            ];

            if ($method === 'pix') {
                $pixData = $this->asaas->getPixData($payment['id']);
                $responseData['pix_code'] = $pixData['payload'] ?? null;
                $responseData['pix_qr'] = $pixData['encodedImage'] ?? null;
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
