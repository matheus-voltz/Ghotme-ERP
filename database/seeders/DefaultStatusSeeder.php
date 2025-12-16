<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cfgconfiguracoes_net')->updateOrInsert(
            [
                'identificacao_validacao' => 'token_correios',
            ],
            [
                'descricao_validacao' => 'Token da API dos Correios',
                'valor_configuracao' => 'kBBwY4NsKiLAZvSOWt75tfdQrMmAgPDvSHqpcb5B',
                'valor_complemento' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}


