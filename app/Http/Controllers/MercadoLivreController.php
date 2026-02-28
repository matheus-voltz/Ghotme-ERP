<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoLivreService;
use App\Models\InventoryItem;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MercadoLivreController extends Controller
{
    /**
     * Redireciona para o Mercado Livre para autorização
     */
    public function redirect()
    {
        try {
            $service = new MercadoLivreService(Auth::user()->company_id);
            $redirectUri = route('meli.callback');
            $url = $service->getAuthUrl($redirectUri);
            
            return redirect($url);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Recebe o código do ML e gera os tokens
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('settings.integrations')
                ->with('error', "Erro na autorização do Mercado Livre: " . $request->error_description);
        }

        if (!$request->has('code')) {
            return redirect()->route('settings.integrations')
                ->with('error', "Código de autorização não recebido.");
        }

        try {
            $service = new MercadoLivreService(Auth::user()->company_id);
            $redirectUri = route('meli.callback');
            $service->handleCallback($request->code, $redirectUri);

            return redirect()->route('settings.integrations')
                ->with('success', "Conta do Mercado Livre conectada com sucesso!");
        } catch (\Exception $e) {
            Log::error("Meli Callback Error: " . $e->getMessage());
            return redirect()->route('settings.integrations')
                ->with('error', "Erro ao processar conexão: " . $e->getMessage());
        }
    }

    /**
     * Publica um item (Produto ou Serviço)
     */
    public function publish(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|in:product,service',
            'price' => 'required|numeric|min:1',
            'category_id' => 'required|string',
        ]);

        try {
            $publishable = $request->type === 'product' 
                ? InventoryItem::findOrFail($request->id)
                : Service::findOrFail($request->id);

            $service = new MercadoLivreService(Auth::user()->company_id);
            $publication = $service->publishItem($publishable, $request->price, $request->category_id);

            return response()->json([
                'success' => true, 
                'message' => 'Anúncio criado com sucesso!',
                'url' => $publication->external_url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
