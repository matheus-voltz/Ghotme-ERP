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

        // Trial logic based on account creation (integers)
        $trialDuration = 30;
        $daysUsed = (int)abs(now()->diffInDays($user->created_at));
        $trialDaysLeft = max(0, $trialDuration - $daysUsed);

        $trialExpired = ($user->plan === 'free' && $trialDaysLeft <= 0);

        $billingHistory = BillingHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Selected Plan Details (if any)
        $selectedPlanDetails = null;
        if ($user->selected_plan && $user->selected_plan !== 'free') {
            $plans = [
                'padrao' => ['monthly' => '149,00', 'yearly' => '1.490,00'],
                'enterprise' => ['monthly' => '279,00', 'yearly' => '2.790,00'],
            ];
            $amount = $plans[$user->selected_plan][$user->plan_type] ?? '0,00';
            $selectedPlanDetails = [
                'name' => ucfirst($user->selected_plan),
                'amount' => $amount,
                'type' => $user->plan_type === 'yearly' ? 'Anual' : 'Mensal'
            ];
        }

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
                'description' => $trialExpired ? 'Seu período de teste grátis expirou!' : 'Você está aproveitando os 30 dias de avaliação gratuita.'
            ];
        }

        return view('content.pages.billing.pages-account-settings-billing', compact('user', 'trialDaysLeft', 'daysUsed', 'billingHistory', 'planDetails', 'trialExpired', 'selectedPlanDetails'));
    }

    public function selectPlan(Request $request)
    {
        $user = auth()->user();
        $plan = $request->plan; // padrao, enterprise
        $type = $request->type; // monthly, yearly

        $validPlans = ['padrao', 'enterprise'];
        if (!in_array($plan, $validPlans)) {
            return response()->json(['success' => false, 'message' => 'Plano inválido.']);
        }

        // Apenas salva a intenção, não gera cobrança ainda
        $user->update([
            'selected_plan' => $plan,
            'plan_type' => $type
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plano selecionado com sucesso! Agora escolha o método de pagamento abaixo.'
        ]);
    }

    public function generatePayment(Request $request)
    {
        $user = auth()->user();
        $method = $request->method; // pix, boleto, credit_card

        // Verifica se selecionou um plano no modal primeiro
        $planToCharge = $user->selected_plan;
        if (!$planToCharge || $planToCharge === 'free') {
            return response()->json(['success' => false, 'message' => 'Por favor, selecione um plano no botão "Escolher um Plano" antes de gerar a cobrança.']);
        }

        $plans = [
            'padrao' => ['monthly' => 149.00, 'yearly' => 1490.00],
            'enterprise' => ['monthly' => 279.00, 'yearly' => 2790.00],
        ];

        $amount = $plans[$planToCharge][$user->plan_type ?? 'monthly'] ?? 149.00;
        $description = "Assinatura Plano " . ucfirst($planToCharge) . " - Ghotme (" . ($user->plan_type === 'yearly' ? 'Anual' : 'Mensal') . ")";

        if (!$user->cpf_cnpj) {
            return response()->json(['success' => false, 'message' => 'Por favor, preencha seu CPF ou CNPJ no perfil antes de gerar a cobrança.']);
        }

        try {
            $customerId = $this->asaas->getOrCreateCustomer($user);

            if ($user->plan_type === 'monthly') {
                // Cria Assinatura Recorrente
                $result = $this->asaas->createSubscription($customerId, $method, $amount, $description);
            } else {
                // Cria Pagamento Único
                $result = $this->asaas->createPayment($customerId, $method, $amount, $description);
            }

            if (!isset($result['id'])) {
                return response()->json(['success' => false, 'message' => $result['errors'][0]['description'] ?? 'Erro no Asaas']);
            }

            $invoiceUrl = $result['invoiceUrl'] ?? null;
            $paymentId = $result['id']; // Default to result ID

            // If it's a subscription, we MUST fetch the first payment's ID and URL
            if ($user->plan_type === 'monthly') {
                $payments = $this->asaas->getSubscriptionPayments($result['id']);
                if (!empty($payments['data'])) {
                    $paymentId = $payments['data'][0]['id'];
                    $invoiceUrl = $payments['data'][0]['invoiceUrl'] ?? $invoiceUrl;
                }
            }

            // Registra no histórico
            BillingHistory::create([
                'user_id' => $user->id,
                'plan_name' => ucfirst($planToCharge) . " (" . ($user->plan_type === 'yearly' ? 'Anual' : 'Mensal') . ")",
                'amount' => $amount,
                'payment_method' => ucfirst($method),
                'payment_url' => $invoiceUrl,
                'status' => 'pending',
            ]);

            $responseData = [
                'success' => true,
                'payment_id' => $paymentId,
                'amount' => number_format($amount, 2, ',', '.'),
                'invoice_url' => $invoiceUrl,
                'bank_slip_url' => $result['bankSlipUrl'] ?? ($payments['data'][0]['bankSlipUrl'] ?? null),
            ];

            if ($method === 'pix') {
                $pixData = $this->asaas->getPixData($paymentId);
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
     * Update the user's billing profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // Basic validation
        $validated = $request->validate([
            'companyName' => 'required|string|max:255',
            'billingEmail' => 'required|email|max:255|unique:users,email,' . $user->id,
            'cpf_cnpj' => 'nullable|string|max:20',
            'mobileNumber' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'billingAddress' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:50',
            'zipCode' => 'nullable|string|max:20',
        ]);

        try {
            $user->update([
                'company' => $validated['companyName'],
                'email' => $validated['billingEmail'],
                'cpf_cnpj' => $validated['cpf_cnpj'],
                'contact_number' => $validated['mobileNumber'],
                'country' => $validated['country'],
                'city' => $validated['city'],
                'billing_address' => $validated['billingAddress'],
                'state' => $validated['state'],
                'zip_code' => $validated['zipCode'],
            ]);

            // Optional: Update Asaas Customer data if needed
            // $this->asaas->updateCustomer($user);

            return response()->json([
                'success' => true,
                'message' => 'Perfil de cobrança atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    }
}
