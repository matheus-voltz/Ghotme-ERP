<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OfxParserService
{
    public function parse($filePath)
    {
        $content = file_get_contents($filePath);
        
        // Limpeza básica para lidar com OFX que não são XML puro (SGML)
        $ofxPart = strstr($content, '<OFX>');
        if (!$ofxPart) return [];

        // Converte SGML para XML básico (fecha tags simples)
        $xmlContent = preg_replace('/<([A-Z0-9_]+)>([^<|\n\r]+)/i', '<$1>$2</$1>', $ofxPart);
        
        try {
            $xml = new \SimpleXMLElement($xmlContent);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao converter OFX para XML: ' . $e->getMessage());
            return [];
        }

        $transactions = [];
        $stmtrs = $xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN;
        $companyId = Auth::user()->company_id;

        foreach ($stmtrs as $trn) {
            $dateStr = (string)$trn->DTPOSTED;
            $date = Carbon::createFromFormat('Ymd', substr($dateStr, 0, 8));
            $amount = (float)$trn->TRNAMT;
            $fitid = (string)$trn->FITID;
            $memo = (string)$trn->MEMO ?: (string)$trn->NAME;
            $type = $amount > 0 ? 'in' : 'out';

            // 1. Verificar se já foi conciliado (já existe FITID no banco)
            $alreadyConciliated = FinancialTransaction::where('company_id', $companyId)
                ->where('bank_transaction_id', $fitid)
                ->first();

            // 2. Tentar encontrar um "Match" automático
            // Busca por valor exato e data próxima (+/- 3 dias) que não esteja conciliado
            $suggestedMatch = null;
            if (!$alreadyConciliated) {
                $suggestedMatch = FinancialTransaction::where('company_id', $companyId)
                    ->where('type', $type)
                    ->where('amount', abs($amount))
                    ->whereNull('bank_transaction_id')
                    ->whereBetween('due_date', [$date->copy()->subDays(3), $date->copy()->addDays(3)])
                    ->first();
            }

            $transactions[] = [
                'bank_id' => $fitid,
                'type' => $type,
                'date' => $date->format('Y-m-d'),
                'amount' => abs($amount),
                'memo' => $memo,
                'status' => $alreadyConciliated ? 'conciliated' : ($suggestedMatch ? 'match_found' : 'new'),
                'match' => $suggestedMatch ? [
                    'id' => $suggestedMatch->id,
                    'description' => $suggestedMatch->description,
                    'due_date' => $suggestedMatch->due_date->format('d/m/Y'),
                ] : null,
                'existing_id' => $alreadyConciliated ? $alreadyConciliated->id : null
            ];
        }

        return $transactions;
    }
}
