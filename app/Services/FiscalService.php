<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\TaxInvoice;
use App\Models\Company;
use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FiscalService
{
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        // Exemplo configurado para a Focus NFe, mas adaptável
        $config = IntegrationSetting::first(); // Em produção, buscar por company_id
        $this->token = $config->fiscal_api_token ?? '';
        $this->baseUrl = ($config->fiscal_environment ?? 'sandbox') === 'production' 
            ? 'https://api.focusnfe.com.br/v2' 
            : 'https://homologacao.focusnfe.com.br/v2';
    }

    /**
     * Transmite uma NF-e baseada em uma Ordem de Serviço
     */
    public function transmitFromOS(OrdemServico $os)
    {
        $company = $os->company ?? Company::find($os->company_id);
        $client = $os->client;

        // Montagem do JSON da Nota (Padrão simplified para exemplo)
        $payload = [
            'natureza_operacao' => 'Venda de mercadoria e prestação de serviço',
            'data_emissao' => now()->toIso8601String(),
            'tipo_documento' => 1, // Saída
            'finalidade_emissao' => 1, // Normal
            'cnpj_emitente' => preg_replace('/\D/', '', $company->document_number),
            'nome_emitente' => $company->name,
            'logradouro_emitente' => $company->address,
            'numero_emitente' => 'SN',
            'bairro_emitente' => 'Centro',
            'municipio_emitente' => $company->city,
            'uf_emitente' => $company->state,
            'inscricao_estadual_emitente' => preg_replace('/\D/', '', $company->ie ?? ''),
            
            'nome_destinatario' => $client->name ?? $client->company_name,
            'cnpj_destinatario' => $client->cnpj ? preg_replace('/\D/', '', $client->cnpj) : null,
            'cpf_destinatario' => $client->cpf ? preg_replace('/\D/', '', $client->cpf) : null,
            'indicador_inscricao_estadual_destinatario' => 9, // Não contribuinte
            
            'items' => $this->formatItems($os),
            'valor_total_nota' => $os->total,
        ];

        try {
            // Chamada real para a API (exemplo Focus NFe)
            // $response = Http::withBasicAuth($this->token, '')->post("{$this->baseUrl}/nfe", $payload);
            
            // Simulação de Sucesso para Teste de Interface
            $fakeResponse = [
                'status' => 'autorizado',
                'numero' => rand(100, 999),
                'serie' => '1',
                'chave_nfe' => '352302' . str_repeat('0', 38),
                'caminho_xml_nota_fiscal' => '/storage/checklists/test.xml',
                'caminho_pdf_nota_fiscal' => '/storage/checklists/test.pdf'
            ];

            // Salva o registro no nosso banco
            return TaxInvoice::create([
                'company_id' => $os->company_id,
                'ordem_servico_id' => $os->id,
                'invoice_number' => $fakeResponse['numero'],
                'status' => 'issued',
                'access_key' => $fakeResponse['chave_nfe'],
                'total_amount' => $os->total,
                'xml_url' => $fakeResponse['caminho_xml_nota_fiscal'],
                'pdf_url' => $fakeResponse['caminho_pdf_nota_fiscal'],
                'issued_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::error("Erro na emissão de nota: " . $e->getMessage());
            throw $e;
        }
    }

    protected function formatItems(OrdemServico $os)
    {
        $items = [];
        
        // Mapear Serviços
        foreach ($os->items as $item) {
            $items[] = [
                'nome' => $item->service->name,
                'quantidade' => $item->quantity,
                'valor_unitario' => $item->price,
                'codigo_ncm' => '99', // Genérico para serviço
            ];
        }

        // Mapear Peças
        foreach ($os->parts as $part) {
            $items[] = [
                'nome' => $part->part->name,
                'quantidade' => $part->quantity,
                'valor_unitario' => $part->price,
                'codigo_ncm' => '87089990', // Exemplo Automotivo
            ];
        }

        return $items;
    }
}
