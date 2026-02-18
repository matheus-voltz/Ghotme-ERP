<?php

namespace App\Services;

class OfxParserService
{
    public function parse($filePath)
    {
        $content = file_get_contents($filePath);
        
        // Limpeza básica para lidar com OFX que não são XML puro (SGML)
        $ofxPart = strstr($content, '<OFX>');
        if (!$ofxPart) return [];

        // Converte SGML para XML básico (fecha tags simples)
        $xmlContent = preg_replace('/<([A-Z0-9_]+)>([^<]+)/i', '<$1>$2</$1>', $ofxPart);
        
        try {
            $xml = new \SimpleXMLElement($xmlContent);
        } catch (\Exception $e) {
            return [];
        }

        $transactions = [];
        // Navega até a lista de transações (Bank Transaction List)
        $stmtrs = $xml->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN;

        foreach ($stmtrs as $trn) {
            $date = (string)$trn->DTPOSTED;
            $formattedDate = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
            
            $transactions[] = [
                'id' => (string)$trn->FITID,
                'type' => (string)$trn->TRNTYPE,
                'date' => $formattedDate,
                'amount' => (float)$trn->TRNAMT,
                'memo' => (string)$trn->MEMO ?: (string)$trn->NAME,
            ];
        }

        return $transactions;
    }
}
