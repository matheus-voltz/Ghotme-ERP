<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Clients;
use App\Models\Company;
use App\Models\FinancialTransaction;
use App\Models\InventoryItem;
use App\Models\OrdemServico;
use App\Models\User;
use App\Models\Vehicles;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get existing company and user
        $company = Company::first() ?? Company::create([
            'name' => 'Oficina Mecânica Demo',
            'email' => 'demo@oficina.com',
            'phone' => '(11) 98888-7777',
        ]);

        $user = User::where('company_id', $company->id)->first() ?? User::create([
            'company_id' => $company->id,
            'name' => 'Demo User',
            'email' => 'demo@demo.com',
            'password' => bcrypt('password'),
        ]);

        // Small helper for random data without Faker
        $firstNames = ['João', 'Maria', 'Jose', 'Ana', 'Carlos', 'Paulo', 'Lucas', 'Mariana', 'Beatriz', 'Ricardo'];
        $lastNames = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Pereira', 'Costa', 'Rodrigues', 'Almeida', 'Nascimento'];
        $companies = ['Transportes XYZ', 'Logística ABC', 'Construções Norte', 'Serviços Gerais LTDA'];

        // 2. Create more Clients
        $clients = [];
        for ($i = 0; $i < 15; $i++) {
            $isPJ = rand(0, 10) > 7;
            $name = $isPJ ? $companies[array_rand($companies)] . ' ' . rand(1, 100) : $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];

            $clients[] = Clients::create([
                'company_id' => $company->id,
                'name' => $name,
                'type' => $isPJ ? 'PJ' : 'PF',
                'email' => strtolower(Str::slug($name)) . rand(1, 99) . '@exemplo.com',
                'phone' => '(11) 9' . rand(7000, 9999) . '-' . rand(1000, 9999),
                'whatsapp' => '(11) 9' . rand(7000, 9999) . '-' . rand(1000, 9999),
            ]);
        }

        // 3. Create Vehicles for clients
        $marcas = ['Toyota', 'Honda', 'Volkswagen', 'Fiat', 'Ford', 'Chevrolet', 'Hyundai'];
        $modelos = [
            'Toyota' => ['Corolla', 'Hilux', 'Etios'],
            'Honda' => ['Civic', 'Fit', 'HR-V'],
            'Volkswagen' => ['Gol', 'Polo', 'Jetta'],
            'Fiat' => ['Uno', 'Palio', 'Toro'],
            'Ford' => ['Ka', 'Fiesta', 'EcoSport'],
            'Chevrolet' => ['Onix', 'Prisma', 'S10'],
            'Hyundai' => ['HB20', 'Creta', 'Tucson'],
        ];

        $vehicles = [];
        foreach ($clients as $client) {
            $count = rand(1, 2);
            for ($i = 0; $i < $count; $i++) {
                $marca = $marcas[array_rand($marcas)];
                $modelo = $modelos[$marca][array_rand($modelos[$marca])];
                $vehicles[] = Vehicles::create([
                    'company_id' => $company->id,
                    'cliente_id' => $client->id,
                    'marca' => $marca,
                    'modelo' => $modelo,
                    'placa' => strtoupper(Str::random(3)) . '-' . rand(1000, 9999),
                    'cor' => ['Branco', 'Preto', 'Cinza', 'Prata', 'Azul', 'Vermelho'][rand(0, 5)],
                    'ano_fabricacao' => rand(2010, 2023),
                    'ano_modelo' => rand(2010, 2024),
                ]);
            }
        }

        // 4. Create OS over the last 6 months
        $osStatuses = ['pending', 'running', 'finalized'];
        for ($i = 0; $i < 60; $i++) {
            $date = Carbon::now()->subDays(rand(0, 180));
            OrdemServico::create([
                'company_id' => $company->id,
                'client_id' => $clients[array_rand($clients)]->id,
                'veiculo_id' => $vehicles[array_rand($vehicles)]->id,
                'user_id' => $user->id,
                'status' => $osStatuses[array_rand($osStatuses)],
                'description' => 'Manutenção Preventiva / Corretiva - OS #' . rand(1000, 9999),
                'km_entry' => rand(5000, 150000),
                'created_at' => $date,
                'updated_at' => $date->copy()->addHours(rand(1, 48)),
            ]);
        }

        // 5. Create Budgets over the last 6 months
        $budgetStatuses = ['pending', 'approved', 'rejected'];
        for ($i = 0; $i < 40; $i++) {
            $date = Carbon::now()->subDays(rand(0, 180));
            Budget::create([
                'company_id' => $company->id,
                'uuid' => (string) Str::uuid(),
                'client_id' => $clients[array_rand($clients)]->id,
                'veiculo_id' => $vehicles[array_rand($vehicles)]->id,
                'user_id' => $user->id,
                'status' => $budgetStatuses[array_rand($budgetStatuses)],
                'valid_until' => $date->copy()->addDays(15),
                'description' => 'Orçamento detalhado para revisão completa do veículo.',
                'created_at' => $date,
                'updated_at' => $date->copy()->addDays(rand(1, 5)),
            ]);
        }

        // 6. Create Financial Transactions over the last 6 months
        for ($i = 0; $i < 100; $i++) {
            $type = rand(0, 1) ? 'in' : 'out';
            $status = rand(0, 10) > 2 ? 'paid' : 'pending';
            $date = Carbon::now()->subDays(rand(0, 180));

            FinancialTransaction::create([
                'company_id' => $company->id,
                'description' => ($type == 'in' ? 'Recebimento: Serviço/Peça' : 'Pagamento: Fornecedor/Despesa'),
                'amount' => rand(50, 2500) + (rand(0, 99) / 100),
                'type' => $type,
                'status' => $status,
                'due_date' => $date->copy()->addDays(rand(-5, 10)),
                'paid_at' => $status == 'paid' ? $date : null,
                'user_id' => $user->id,
                'created_at' => $date,
            ]);
        }

        // 7. Inventory Items (Low Stock Demo)
        $items = ['Óleo 5W30', 'Filtro de Ar', 'Pastilha de Freio', 'Lâmpada Farol', 'Correia Dentada'];
        foreach ($items as $itemName) {
            InventoryItem::create([
                'company_id' => $company->id,
                'name' => $itemName . ' (Demo)',
                'cost_price' => rand(20, 150),
                'selling_price' => rand(160, 300),
                'quantity' => rand(1, 3),
                'min_quantity' => rand(5, 10),
                'is_active' => true,
            ]);
        }

        $this->command->info('Dados de demonstração gerados SEM Faker com sucesso!');
    }
}
