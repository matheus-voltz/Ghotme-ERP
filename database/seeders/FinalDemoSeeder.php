<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FinalDemoSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            'users', 'companies', 'clients', 'veiculos', 'inventory_items',
            'services', 'budgets', 'budget_items', 'budget_parts',
            'ordem_servicos', 'ordem_servico_items', 'ordem_servico_parts',
            'financial_transactions', 'suppliers', 'chat_messages',
            'appointments', 'stock_movements', 'tax_invoices'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();

        $niches = [
            'automotive' => [
                'company' => 'Auto Center Precision',
                'entities' => [
                    ['marca' => 'Toyota', 'modelo' => 'Corolla', 'placa' => 'ABC-1234'],
                    ['marca' => 'Honda', 'modelo' => 'Civic', 'placa' => 'XYZ-9876']
                ],
                'items' => [
                    ['name' => 'Óleo 5W30', 'price' => 60.00],
                    ['name' => 'Filtro de Ar', 'price' => 45.00]
                ],
                'services' => [
                    ['name' => 'Revisão Completa', 'price' => 450.00],
                    ['name' => 'Troca de Freios', 'price' => 220.00]
                ]
            ],
            'electronics' => [
                'company' => 'Tech Repair Solutions',
                'entities' => [
                    ['marca' => 'Apple', 'modelo' => 'iPhone 15 Pro', 'placa' => 'SN-99228811'],
                    ['marca' => 'Samsung', 'modelo' => 'Galaxy S24', 'placa' => 'SN-11223344']
                ],
                'items' => [
                    ['name' => 'Tela OLED', 'price' => 850.00],
                    ['name' => 'Bateria 5000mAh', 'price' => 180.00]
                ],
                'services' => [
                    ['name' => 'Troca de Tela', 'price' => 200.00],
                    ['name' => 'Limpeza Química', 'price' => 150.00]
                ]
            ],
            'pet' => [
                'company' => 'Pet Care & Health',
                'entities' => [
                    ['marca' => 'Cão', 'modelo' => 'Golden Retriever', 'placa' => 'Thor'],
                    ['marca' => 'Gato', 'modelo' => 'Persa', 'placa' => 'Luna']
                ],
                'items' => [
                    ['name' => 'Vacina V10', 'price' => 120.00],
                    ['name' => 'Ração Premium 10kg', 'price' => 250.00]
                ],
                'services' => [
                    ['name' => 'Banho e Tosa', 'price' => 90.00],
                    ['name' => 'Consulta Veterinária', 'price' => 180.00]
                ]
            ],
            'beauty_clinic' => [
                'company' => 'Estética Avançada Royal',
                'entities' => [
                    ['marca' => 'Feminino', 'modelo' => 'Protocolo Rejuvenescimento', 'placa' => 'Maria Oliveira'],
                    ['marca' => 'Feminino', 'modelo' => 'Protocolo Corporal', 'placa' => 'Fernanda Santos']
                ],
                'items' => [
                    ['name' => 'Ácido Hialurônico 1ml', 'price' => 1200.00],
                    ['name' => 'Creme Hidratante Facial', 'price' => 150.00]
                ],
                'services' => [
                    ['name' => 'Aplicação de Botox', 'price' => 950.00],
                    ['name' => 'Limpeza de Pele Profunda', 'price' => 250.00]
                ]
            ]
        ];

        foreach ($niches as $nicheKey => $nicheData) {
            // 1. Company
            $companyId = DB::table('companies')->insertGetId([
                'name' => $nicheData['company'],
                'slug' => Str::slug($nicheData['company']), // Added slug
                'niche' => $nicheKey,
                'email' => "contato@" . $nicheKey . ".com",
                'document_number' => rand(10000000000000, 99999999999999),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Users
            $roles = ['admin', 'user', 'user']; // admin, recepcao, técnico
            $names = ['Admin', 'Atendente', 'Especialista'];
            $userIds = [];

            foreach ($roles as $idx => $role) {
                $userIds[] = DB::table('users')->insertGetId([
                    'name' => $names[$idx] . ' ' . ucfirst($nicheKey),
                    'email' => strtolower($names[$idx]) . "@" . $nicheKey . ".com",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'company_id' => $companyId,
                    'role' => $role,
                    'niche' => $nicheKey,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Items & Services
            $inventoryIds = [];
            foreach ($nicheData['items'] as $item) {
                $inventoryIds[] = DB::table('inventory_items')->insertGetId([
                    'company_id' => $companyId,
                    'name' => $item['name'],
                    'cost_price' => $item['price'] * 0.5,
                    'selling_price' => $item['price'],
                    'quantity' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $serviceIds = [];
            foreach ($nicheData['services'] as $service) {
                $serviceIds[] = DB::table('services')->insertGetId([
                    'company_id' => $companyId,
                    'name' => $service['name'],
                    'price' => $service['price'],
                    'estimated_time' => 60,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Clients & Entities
            foreach ($nicheData['entities'] as $idx => $entity) {
                $clientId = DB::table('clients')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $companyId,
                    'name' => "Cliente " . ($idx + 1) . " " . ucfirst($nicheKey),
                    'type' => 'PF',
                    'email' => "cliente".($idx+1)."@".$nicheKey.".com",
                    'cpf' => rand(10000000000, 99999999999),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $entityId = DB::table('veiculos')->insertGetId([
                    'company_id' => $companyId,
                    'cliente_id' => $clientId,
                    'marca' => $entity['marca'],
                    'modelo' => $entity['modelo'],
                    'placa' => $entity['placa'],
                    'ano_modelo' => 2024,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 5. Budget & OS
                $budgetId = DB::table('budgets')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $companyId,
                    'client_id' => $clientId,
                    'veiculo_id' => $entityId,
                    'status' => 'approved',
                    'valid_until' => now()->addDays(7),
                    'created_at' => now()->subDays(2),
                    'updated_at' => now(),
                ]);

                $osId = DB::table('ordem_servicos')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $companyId,
                    'client_id' => $clientId,
                    'veiculo_id' => $entityId,
                    'status' => 'in_progress',
                    'user_id' => $userIds[2], // Specialist
                    'scheduled_at' => now(),
                    'created_at' => now()->subDay(),
                    'updated_at' => now(),
                ]);

                // Items in OS
                DB::table('ordem_servico_items')->insert([
                    'ordem_servico_id' => $osId,
                    'service_id' => $serviceIds[0],
                    'price' => $nicheData['services'][0]['price'],
                    'quantity' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('ordem_servico_parts')->insert([
                    'ordem_servico_id' => $osId,
                    'inventory_item_id' => $inventoryIds[0],
                    'price' => $nicheData['items'][0]['price'],
                    'quantity' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
