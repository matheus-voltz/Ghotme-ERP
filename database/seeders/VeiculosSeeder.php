<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VeiculosSeeder extends Seeder
{
    public function run(): void
    {
        $veiculos = [
            [
                'placa' => 'ABC1D23',
                'cliente_id' => 1,
                'renavam' => '12345678901',
                'chassi' => '9BWZZZ377VT004251',
                'marca' => 'Volkswagen',
                'modelo' => 'Gol',
                'versao' => '1.6 MSI',
                'ano_fabricacao' => 2020,
                'ano_modelo' => 2021,
                'cor' => 'Prata',
                'combustivel' => 'Flex',
                'cambio' => 'Manual',
                'motor' => '1.6',
                'km_atual' => 45000,
                'ultima_revisao' => '2025-06-10',
                'proxima_revisao' => '2026-06-10',
                'ativo' => true,
                'origem' => 'sistema',
                'criado_por' => 1,
                'atualizado_por' => 1,
            ],
            [
                'placa' => 'DEF4G56',
                'cliente_id' => 2,
                'renavam' => '98765432100',
                'chassi' => '9C2KC1670HR004321',
                'marca' => 'Honda',
                'modelo' => 'Civic',
                'versao' => '2.0 EX',
                'ano_fabricacao' => 2019,
                'ano_modelo' => 2020,
                'cor' => 'Preto',
                'combustivel' => 'Gasolina',
                'cambio' => 'Automático',
                'motor' => '2.0',
                'km_atual' => 72000,
                'ultima_revisao' => '2025-04-15',
                'proxima_revisao' => '2026-04-15',
                'ativo' => true,
                'origem' => 'sistema',
                'criado_por' => 1,
                'atualizado_por' => 1,
            ],
            [
                'placa' => 'GHI7J89',
                'cliente_id' => 3,
                'renavam' => '45678912345',
                'chassi' => '8AFZZZ377VT009876',
                'marca' => 'Toyota',
                'modelo' => 'Corolla',
                'versao' => '1.8 XEi',
                'ano_fabricacao' => 2011,
                'ano_modelo' => 2012,
                'cor' => 'Branco',
                'combustivel' => 'Flex',
                'cambio' => 'Automático',
                'motor' => '1.8',
                'km_atual' => 38000,
                'ultima_revisao' => '2025-08-20',
                'proxima_revisao' => '2026-08-20',
                'ativo' => true,
                'origem' => 'sistema',
                'criado_por' => 1,
                'atualizado_por' => 1,
            ],
        ];

        foreach ($veiculos as $veiculo) {
            $veiculo['updated_at'] = now();

            // Se não existir, insere com UUID e created_at
            if (!DB::table('veiculos')->where('placa', $veiculo['placa'])->exists()) {
                $veiculo['uuid'] = (string) Str::uuid();
                $veiculo['created_at'] = now();
                DB::table('veiculos')->insert($veiculo);
            } else {
                // Se existir, apenas atualiza
                DB::table('veiculos')->where('placa', $veiculo['placa'])->update($veiculo);
            }
        }
    }
}
