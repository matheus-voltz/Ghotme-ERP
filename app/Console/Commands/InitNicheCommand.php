<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Company;
use App\Services\NicheInitializerService;

class InitNicheCommand extends Command
{
    /**
     * O nome e a assinatura do comando.
     * Exemplo: php artisan ghotme:init-niche 1 pet
     */
    protected $signature = 'ghotme:init-niche {user_id} {niche}';

    /**
     * A descrição do comando.
     */
    protected $description = 'Altera o nicho de um usuário e inicializa os dados padrão (serviços, checklist) para esse nicho.';

    /**
     * Executa o comando.
     */
    public function handle(NicheInitializerService $initializer)
    {
        $userId = $this->argument('user_id');
        $niche = $this->argument('niche');

        // Lista de nichos suportados no config/niche.php
        $supportedNiches = ['automotive', 'pet', 'electronics', 'beauty_clinic'];

        if (!in_array($niche, $supportedNiches)) {
            $this->error("Nicho '{$niche}' não suportado. Use: " . implode(', ', $supportedNiches));
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário ID {$userId} não encontrado.");
            return;
        }

        $companyId = $user->company_id;
        if (!$companyId) {
            // Se o usuário não tiver empresa, tenta associar à primeira ou avisa
            $company = Company::first();
            if (!$company) {
                $this->error("Nenhuma empresa encontrada no sistema.");
                return;
            }
            $user->update(['company_id' => $company->id]);
            $companyId = $company->id;
        }

        $this->info("⚙️ Alterando nicho para: {$niche}...");

        // 1. Atualiza o nicho no usuário (para o NicheHelper funcionar)
        $user->update(['niche' => $niche]);

        // 2. Inicializa os dados (Serviços e Checklist)
        $initializer->initialize($companyId, $niche);

        $this->info("✅ Sucesso! O usuário {$user->name} agora está no nicho '{$niche}'.");
        $this->info("🚀 Dados de exemplo (Serviços e Checklist) foram carregados para a empresa ID {$companyId}.");
        $this->info("💡 Agora, faça login com este usuário e veja a mágica nas telas de OS e Inventário.");
    }
}
