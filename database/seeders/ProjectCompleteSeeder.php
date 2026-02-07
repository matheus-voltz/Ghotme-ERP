<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Clients;
use App\Models\Company;
use App\Models\FinancialTransaction;
use App\Models\InventoryItem;
use App\Models\OrdemServico;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProjectCompleteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar a Empresa Principal
        $company = Company::create([
            'name' => 'Ghotme Auto Center',
            'email' => 'contato@ghotme.com',
            'phone' => '(11) 4002-8922',
            'address' => 'Av. das Nações Unidas, 1000',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);

        // 2. Criar o Usuário Administrador
        User::create([
            'company_id' => $company->id,
            'name' => 'Administrador Ghotme',
            'email' => 'admin@ghotme.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // 3. Criar Fornecedores
        $suppliers = [];
        $supplierNames = ['Distribuidora de Peças Silva', 'Auto Elétrica Central', 'Pneus e Cia'];
        foreach ($supplierNames as $name) {
            $suppliers[] = Supplier::create([
                'company_id' => $company->id,
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@teste.com',
                'phone' => '(11) 9' . rand(7000, 9999) . '-' . rand(1000, 9999),
                'is_active' => true,
            ]);
        }

        // 4. Criar Itens de Estoque (Peças)
        $parts = [
            ['name' => 'Filtro de Óleo', 'price' => 45.00],
            ['name' => 'Pastilha de Freio Dianteira', 'price' => 120.00],
            ['name' => 'Óleo 5W30 Sintético', 'price' => 38.00],
            ['name' => 'Amortecedor Dianteiro', 'price' => 350.00],
            ['name' => 'Lâmpada H7', 'price' => 15.00],
        ];

        foreach ($parts as $p) {
            InventoryItem::create([
                'company_id' => $company->id,
                'supplier_id' => $suppliers[array_rand($suppliers)]->id,
                'name' => $p['name'],
                'selling_price' => $p['price'],
                'cost_price' => $p['price'] * 0.6,
                'quantity' => rand(10, 50),
                'min_quantity' => 5,
                'is_active' => true,
            ]);
        }

        // 5. Criar Serviços
        $services = [
            ['name' => 'Troca de Óleo', 'price' => 60.00],
            ['name' => 'Alinhamento e Balanceamento', 'price' => 150.00],
            ['name' => 'Limpeza de Bicos', 'price' => 180.00],
            ['name' => 'Revisão Geral', 'price' => 250.00],
            ['name' => 'Mão de Obra Mecânica (Hora)', 'price' => 120.00],
        ];

        foreach ($services as $s) {
            Service::create([
                'company_id' => $company->id,
                'name' => $s['name'],
                'price' => $s['price'],
                'is_active' => true,
            ]);
        }

        // 6. Formas de Pagamento
        $methods = ['Dinheiro', 'Cartão de Crédito', 'PIX', 'Boleto'];
        foreach ($methods as $m) {
            PaymentMethod::create([
                'company_id' => $company->id,
                'name' => $m,
                'is_active' => true,
            ]);
        }

        // 7. Criar Clientes e Veículos
        $clientsData = [
            ['name' => 'Carlos Alberto', 'type' => 'PF'],
            ['name' => 'Maria Oliveira', 'type' => 'PF'],
            ['name' => 'Transportes Rápidos LTDA', 'type' => 'PJ'],
        ];

        $vehiclesData = [
            ['marca' => 'Volkswagen', 'modelo' => 'Gol', 'placa' => 'GHT-0001'],
            ['marca' => 'Honda', 'modelo' => 'Civic', 'placa' => 'GHT-0002'],
            ['marca' => 'Fiat', 'modelo' => 'Uno', 'placa' => 'GHT-0003'],
            ['marca' => 'Ford', 'modelo' => 'Ka', 'placa' => 'GHT-0004'],
        ];

        foreach ($clientsData as $c) {
            $client = Clients::create([
                'company_id' => $company->id,
                'name' => $c['name'],
                'type' => $c['type'],
                'email' => strtolower(explode(' ', $c['name'])[0]) . '@email.com',
                'phone' => '(11) 98888-' . rand(1000, 9999),
            ]);

            // Criar 1 ou 2 veículos para cada cliente
            for ($i = 0; $i < rand(1, 2); $i++) {
                $v = $vehiclesData[array_rand($vehiclesData)];
                Vehicles::create([
                    'company_id' => $company->id,
                    'cliente_id' => $client->id,
                    'marca' => $v['marca'],
                    'modelo' => $v['modelo'],
                    'placa' => substr($v['placa'], 0, 4) . rand(1000, 9999),
                    'cor' => 'Preto',
                    'ano_fabricacao' => 2020,
                    'ano_modelo' => 2021,
                ]);
            }
        }

        // 8. Criar alguns Orçamentos
        $allClients = Clients::all();
        $allVehicles = Vehicles::all();

        foreach (range(1, 5) as $i) {
            Budget::create([
                'company_id' => $company->id,
                'uuid' => (string) Str::uuid(),
                'client_id' => $allClients->random()->id,
                'veiculo_id' => $allVehicles->random()->id,
                'user_id' => 1,
                'status' => $i % 2 == 0 ? 'pending' : 'approved',
                'valid_until' => now()->addDays(15),
                'description' => 'Orçamento de teste gerado automaticamente.',
            ]);
        }

        // 9. Criar algumas OS (Ordens de Serviço)
        foreach (range(1, 5) as $i) {
            OrdemServico::create([
                'company_id' => $company->id,
                'client_id' => $allClients->random()->id,
                'veiculo_id' => $allVehicles->random()->id,
                'user_id' => 1,
                'status' => $i % 3 == 0 ? 'completed' : 'in_progress',
                'description' => 'Manutenção preventiva periódica.',
                'km_entry' => rand(10000, 90000),
            ]);
        }

        $this->command->info('Dados mestres gerados com sucesso!');
        $this->command->info('Acesse com: admin@ghotme.com / password');
    }
}