<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VehicleLookupController extends Controller
{
    /**
     * Consulta dados do veículo pela placa.
     *
     * @param  string  $placa
     * @return \Illuminate\Http\JsonResponse
     */
    public function lookup($placa)
    {
        // Limpa a placa (apenas letras e números)
        $placa = preg_replace('/[^a-zA-Z0-9]/', '', $placa);

        if (strlen($placa) < 7) {
            return response()->json(['error' => 'Placa inválida'], 400);
        }

        // Exemplo de integração com API (Substitua pela sua API preferida)
        // Aqui estou usando um serviço público de exemplo ou mockando para teste.
        // APIs sugeridas: Kepller, Infosimples, ou Sinesp (não oficial).
        
        // Simulação para testes locais (remova em produção se tiver API real)
        /*
        if ($placa === 'ABC1234') {
            return response()->json([
                'marca' => 'Volkswagen',
                'modelo' => 'Gol 1.6 MSI',
                'ano' => 2020,
                'cor' => 'Branco',
                'chassi' => '9BW...'
            ]);
        }
        */

        try {
            // Tenta consultar uma API pública (exemplo fictício, pois APIs de placa mudam muito)
            // $response = Http::get("https://api-exemplo.com/placa/{$placa}");
            
            // Para este exemplo, vamos retornar um erro 404 para que o front entenda que 
            // não achou e deixe o usuário digitar, ou você pode conectar sua API paga aqui.
            
            // return $response->json();
            
            return response()->json(['message' => 'API de consulta não configurada'], 404);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao consultar placa'], 500);
        }
    }
}
