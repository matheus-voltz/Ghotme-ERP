<?php
$file = '/Users/matheusghotme/Ghotme/app/Http/Controllers/AiConsultantController.php';
$content = file_get_contents($file);

$start = '// Busca contexto do negócio';
$end = 'return response()->json([\'success\' => false, \'message\' => \'Erro ao processar consulta.\'], 500);';

$pos_start = strpos($content, $start);
$pos_end = strpos($content, $end);

if ($pos_start !== false && $pos_end !== false) {
    $pos_end += strlen($end);
    $replacement = "        // Dispara o processamento da IA em Background (Job)\n        \\App\\Jobs\\ProcessAiConsultantMessage::dispatch(\$chat->id, \$userMessage, \$user, \$company);\n\n        return response()->json([\n            'success' => true,\n            'message' => 'Sua mensagem está sendo processada. A IA enviará a resposta em instantes.',\n            'pending' => true\n        ]);";
    
    $new_content = substr($content, 0, $pos_start) . $replacement . substr($content, $pos_end);
    file_put_contents($file, $new_content);
    echo "Sucesso!";
} else {
    echo "Não encontrou os blocos";
}
