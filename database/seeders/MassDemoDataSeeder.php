<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = DB::table('companies')->get();

        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Por favor rode o FinalDemoSeeder antes.');
            return;
        }

        $names = ['Ana Silva', 'Carlos Santos', 'Fernanda Souza', 'José Oliveira', 'Mariana Costa', 'Paulo Rocha', 'Roberto Mendes', 'Camila Dias', 'Lucas Ferreira', 'Beatriz Alves', 'João Pereira', 'Julia Gomes', 'Rafael Ribeiro', 'Amanda Martins', 'Diego Carvalho', 'Bruna Pinto', 'Thiago Melo', 'Letícia Castro', 'Gabriel Barbosa', 'Larissa Correia'];
        $adjectives = ['Pro', 'Max', 'Ultra', 'Plus', 'Premium', 'Eco', 'Turbo', 'Smart', 'Elite', 'Fast'];
        $categories = ['Básico', 'Avançado', 'Especial', 'VIP', 'Master'];

        foreach ($companies as $company) {
            $this->command->info("Gerando dados para: {$company->name} ({$company->niche})");

            $clientsIds = [];
            $inventoryIds = [];
            $servicesIds = [];
            $veiculosIds = [];
            $usersIds = DB::table('users')->where('company_id', $company->id)->pluck('id')->toArray();

            // Se não houver usuários, pega nulo (dependendo da sua database)
            $userId = !empty($usersIds) ? $usersIds[0] : null;

            // 1. Criar 20 Clientes
            for ($i = 0; $i < 20; $i++) {
                $name = $names[array_rand($names)] . ' ' . rand(100, 999);
                $clientsIds[] = DB::table('clients')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'name' => $name,
                    'type' => rand(0, 1) ? 'PF' : 'PJ',
                    'email' => 'cliente_' . $company->id . '_' . $i . '_' . rand(1000, 9999) . '@exemplo.com',
                    'phone' => '(11) 9' . rand(1000, 9999) . '-' . rand(1000, 9999),
                    'cpf' => str_pad(rand(100000000, 999999999), 11, '0', STR_PAD_LEFT),
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now(),
                ]);
            }

            // 2. Criar 30 Peças / Itens de Estoque
            for ($i = 0; $i < 30; $i++) {
                $cost = rand(10, 500) + (rand(0, 99) / 100);
                $inventoryIds[] = DB::table('inventory_items')->insertGetId([
                    'company_id' => $company->id,
                    'name' => 'Produto ' . $adjectives[array_rand($adjectives)] . ' ' . rand(10, 99),
                    'sku' => 'ITM-' . $company->id . '-' . $i . '-' . rand(100, 999),
                    'cost_price' => $cost,
                    'selling_price' => $cost * (rand(13, 25) / 10),
                    'quantity' => rand(5, 100),
                    'min_quantity' => rand(1, 4),
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now(),
                ]);
            }

            // 3. Obter ou Criar Serviços
            $existingServices = DB::table('services')->where('company_id', $company->id)->pluck('id')->toArray();
            if (empty($existingServices)) {
                for ($i = 0; $i < 10; $i++) {
                    $existingServices[] = DB::table('services')->insertGetId([
                        'company_id' => $company->id,
                        'name' => 'Serviço ' . $categories[array_rand($categories)] . ' ' . rand(1, 10),
                        'price' => rand(50, 1000) + (rand(0, 99) / 100),
                        'estimated_time' => rand(30, 240),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $servicesIds = $existingServices;

            // 4. Criar Entidades (Veículos / Pets / Aparelhos)
            foreach ($clientsIds as $clientId) {
                $qtd = rand(1, 2);
                for ($j = 0; $j < $qtd; $j++) {

                    $marca = 'Marca ' . rand(1, 5);
                    $modelo = 'Modelo ' . rand(1, 5);
                    // Adicionamos ID do cliente e o índice $j para garantir uma Placa 100% EXCLUSIVA (evita Unique DB Constraints)
                    $placaSuffix = '-' . $clientId . $j;

                    if ($company->niche == 'automotive') {
                        $marcas = ['Chevrolet', 'Fiat', 'Volkswagen', 'Ford', 'Toyota', 'Honda'];
                        $modelos = ['Sedan', 'Hatch', 'SUV', 'Picape'];
                        $marca = $marcas[array_rand($marcas)];
                        $modelo = $modelos[array_rand($modelos)];
                        // Ex: ABC-55C10
                        $placa = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . '-' . rand(10, 99) . 'C' . $clientId . $j;
                    } elseif ($company->niche == 'pet') {
                        $marcas = ['Cão', 'Gato', 'Ave', 'Roedor'];
                        $modelos = ['Vira-lata', 'Poodle', 'Bulldog', 'Persa', 'Siamês'];
                        $nomes_pets = ['Rex', 'Bolinha', 'Thor', 'Luna', 'Mel', 'Nina'];
                        $marca = $marcas[array_rand($marcas)];
                        $modelo = $modelos[array_rand($modelos)];
                        $placa = $nomes_pets[array_rand($nomes_pets)] . ' C' . $clientId . '-' . $j;
                    } elseif ($company->niche == 'electronics') {
                        $marcas = ['Samsung', 'Apple', 'Motorola', 'Dell', 'HP', 'LG'];
                        $modelos = ['Smartphone', 'Notebook', 'Tablet', 'Monitor'];
                        $marca = $marcas[array_rand($marcas)];
                        $modelo = $modelos[array_rand($modelos)] . ' ' . $adjectives[array_rand($adjectives)];
                        $placa = 'SN-' . rand(10000, 99999) . '-' . $clientId . $j;
                    } elseif ($company->niche == 'beauty_clinic') {
                        $marcas = ['Feminino', 'Masculino'];
                        $modelos = ['Tratamento Facial', 'Tratamento Corporal', 'Manutenção'];
                        $marca = $marcas[array_rand($marcas)];
                        $modelo = $modelos[array_rand($modelos)];
                        $placa = 'Paciente C' . $clientId . '-' . $j;
                    } else {
                        $placa = 'ID-' . rand(1000, 9999) . $placaSuffix;
                    }

                    $veiculosIds[] = DB::table('veiculos')->insertGetId([
                        'company_id' => $company->id,
                        'cliente_id' => $clientId,
                        'marca' => $marca,
                        'modelo' => $modelo,
                        'placa' => $placa,
                        'ano_modelo' => rand(2015, 2024),
                        'cor' => 'Cor ' . rand(1, 10),
                        'created_at' => now()->subDays(rand(1, 365)),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 5. Criar 15 Orçamentos
            $budgetStatuses = ['pending', 'approved', 'rejected', 'expired'];
            for ($i = 0; $i < 15; $i++) {
                $vehId = $veiculosIds[array_rand($veiculosIds)];
                $cId = DB::table('veiculos')->where('id', $vehId)->value('cliente_id');

                $budgetId = DB::table('budgets')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'client_id' => $cId,
                    'veiculo_id' => $vehId,
                    'status' => $budgetStatuses[array_rand($budgetStatuses)],
                    'valid_until' => now()->addDays(rand(1, 15)),
                    'notes' => 'Orçamento gerado automaticamente para testes.',
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now(),
                ]);

                $itemsQtd = rand(1, 3);
                for ($k = 0; $k < $itemsQtd; $k++) {
                    DB::table('budget_items')->insert([
                        'budget_id' => $budgetId,
                        'service_id' => $servicesIds[array_rand($servicesIds)],
                        'price' => rand(50, 500) + (rand(0, 99) / 100),
                        'quantity' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (!empty($inventoryIds)) {
                    $partsQtd = rand(0, 3);
                    for ($k = 0; $k < $partsQtd; $k++) {
                        DB::table('budget_parts')->insert([
                            'budget_id' => $budgetId,
                            'inventory_item_id' => $inventoryIds[array_rand($inventoryIds)],
                            'price' => rand(10, 200) + (rand(0, 99) / 100),
                            'quantity' => rand(1, 4),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // 6. Criar 15 Ordens de Serviço
            $osStatuses = ['pending', 'in_progress', 'completed', 'canceled'];
            for ($i = 0; $i < 15; $i++) {
                $vehId = $veiculosIds[array_rand($veiculosIds)];
                $cId = DB::table('veiculos')->where('id', $vehId)->value('cliente_id');

                $osId = DB::table('ordem_servicos')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'client_id' => $cId,
                    'veiculo_id' => $vehId,
                    'user_id' => $userId,
                    'status' => $osStatuses[array_rand($osStatuses)],
                    'description' => 'Sintoma diagnosticado e analisado. Requere reparos imediatos.',
                    'scheduled_at' => now()->addDays(rand(-30, 30)),
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now(),
                ]);

                $itemsQtd = rand(1, 3);
                for ($k = 0; $k < $itemsQtd; $k++) {
                    DB::table('ordem_servico_items')->insert([
                        'ordem_servico_id' => $osId,
                        'service_id' => $servicesIds[array_rand($servicesIds)],
                        'price' => rand(50, 500) + (rand(0, 99) / 100),
                        'quantity' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (!empty($inventoryIds)) {
                    $partsQtd = rand(0, 3);
                    for ($k = 0; $k < $partsQtd; $k++) {
                        DB::table('ordem_servico_parts')->insert([
                            'ordem_servico_id' => $osId,
                            'inventory_item_id' => $inventoryIds[array_rand($inventoryIds)],
                            'price' => rand(10, 200) + (rand(0, 99) / 100),
                            'quantity' => rand(1, 4),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            $this->command->info("✓ 20 Clientes, 30 Peças, 15 Orçamentos e 15 OS geradas para {$company->name}");
        }
    }
}
