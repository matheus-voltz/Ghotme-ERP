<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clients;
use App\Models\Company;
use App\Models\FinancialTransaction;
use App\Models\InventoryItem;
use App\Models\OrdemServico;
use App\Models\OrdemServicoItem;
use App\Models\OrdemServicoPart;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicles;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReportsDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get/Create context
        $company = Company::first() ?? Company::create(['name' => 'Demo Enterprise']);
        $user = User::where('company_id', $company->id)->first();
        
        if (!$user) {
            $user = User::create([
                'company_id' => $company->id,
                'name' => 'Admin Demo',
                'email' => 'admin@demo.com',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]);
        }
        
        $this->command->info("Gerando dados para: " . $company->name);

        // 2. Payment Methods
        $paymentMethodsData = [
            ['name' => 'Cartão de Crédito', 'type' => 'credit_card'],
            ['name' => 'Cartão de Débito', 'type' => 'debit_card'],
            ['name' => 'Pix', 'type' => 'pix'],
            ['name' => 'Dinheiro', 'type' => 'cash'],
            ['name' => 'Boleto', 'type' => 'bank_slip'],
        ];

        $pmModels = [];
        foreach ($paymentMethodsData as $pm) {
            $pmModels[] = PaymentMethod::firstOrCreate(['name' => $pm['name']], ['type' => $pm['type'], 'is_active' => true]);
        }

        // 3. Suppliers
        $suppliersNames = ['Auto Peças Silva', 'Distribuidora Norte', 'Mecânica Total SA', 'Peças e Cia', 'Filtros Express'];
        $suppliers = [];
        foreach ($suppliersNames as $name) {
            $suppliers[] = Supplier::create([
                'company_id' => $company->id,
                'name' => $name,
                'trade_name' => $name . ' LTDA',
                'email' => strtolower(Str::slug($name)) . '@exemplo.com',
                'phone' => '(11) 9' . rand(7000, 9999) . '-' . rand(1000, 9999),
                'is_active' => true,
            ]);
        }

        // 4. Ensure we have some Services and Inventory Items
        $services = Service::where('company_id', $company->id)->get();
        if ($services->isEmpty()) {
            $serviceNames = ['Troca de Óleo', 'Alinhamento', 'Balanceamento', 'Revisão Geral', 'Lavagem', 'Troca de Freios'];
            foreach ($serviceNames as $name) {
                $services->push(Service::create([
                    'company_id' => $company->id,
                    'name' => $name,
                    'price' => rand(50, 500),
                    'is_active' => true
                ]));
            }
        }

        $items = InventoryItem::where('company_id', $company->id)->get();
        if ($items->isEmpty()) {
            $itemNames = ['Filtro de Óleo', 'Pastilha de Freio', 'Lâmpada H7', 'Pneu 175/70 R14', 'Amortecedor'];
            foreach ($itemNames as $index => $name) {
                $qty = ($index == 0) ? 2 : rand(10, 50); // Item 0 will be low stock
                $items->push(InventoryItem::create([
                    'company_id' => $company->id,
                    'name' => $name,
                    'cost_price' => rand(10, 100),
                    'selling_price' => rand(110, 300),
                    'quantity' => $qty,
                    'min_quantity' => 5,
                    'is_active' => true
                ]));
            }
        } else {
            // Update one existing item to be low stock
            $lowStockItem = $items->first();
            $lowStockItem->update(['quantity' => 2, 'min_quantity' => 5]);
        }

        $clients = Clients::where('company_id', $company->id)->get();
        $vehicles = Vehicles::where('company_id', $company->id)->get();
        $users = User::where('company_id', $company->id)->get();

        if ($clients->isEmpty() || $vehicles->isEmpty() || $users->isEmpty()) {
            $this->command->error("Não há dados suficientes.");
            return;
        }

        // 5. Generate historical OS (Last 6 months)
        $this->command->info("Criando 100 Ordens de Serviço históricas...");

        for ($i = 0; $i < 100; $i++) {
            $date = Carbon::now()->subDays(rand(1, 180));
            
            $client = $clients->random();
            $vehicle = $vehicles->where('client_id', $client->id)->first() ?? $vehicles->random();
            $status = rand(0, 10) > 2 ? 'completed' : 'in_progress';
            $osUser = $users->random();

            $os = OrdemServico::create([
                'company_id' => $company->id,
                'client_id' => $client->id,
                'veiculo_id' => $vehicle->id,
                'user_id' => $osUser->id,
                'status' => $status,
                'description' => 'Serviço preventivo ' . rand(100, 999),
                'km_entry' => rand(1000, 200000),
                'created_at' => $date,
                'updated_at' => $date->copy()->addHours(rand(1, 24)),
            ]);

            // Items
            $totalOS = 0;
            for ($j = 0; $j < rand(1, 2); $j++) {
                $service = $services->random();
                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'service_id' => $service->id,
                    'price' => $service->price,
                    'quantity' => 1,
                    'status' => $status == 'completed' ? 'completed' : 'pending',
                    'created_at' => $date,
                ]);
                $totalOS += $service->price;
            }

            // Parts
            for ($k = 0; $k < rand(0, 3); $k++) {
                $item = $items->random();
                $qty = rand(1, 2);
                OrdemServicoPart::create([
                    'ordem_servico_id' => $os->id,
                    'inventory_item_id' => $item->id,
                    'price' => $item->selling_price,
                    'quantity' => $qty,
                    'created_at' => $date,
                ]);
                $totalOS += ($item->selling_price * $qty);

                \App\Models\StockMovement::create([
                    'company_id' => $company->id,
                    'inventory_item_id' => $item->id,
                    'type' => 'out',
                    'quantity' => $qty,
                    'unit_price' => $item->selling_price,
                    'reason' => 'Venda OS #' . $os->id,
                    'user_id' => $user->id,
                    'created_at' => $date,
                ]);
            }

            // Financial
            if ($status == 'completed') {
                FinancialTransaction::create([
                    'company_id' => $company->id,
                    'description' => 'Pagamento OS #' . $os->id,
                    'amount' => $totalOS,
                    'type' => 'in',
                    'status' => 'paid',
                    'due_date' => $date,
                    'paid_at' => $date,
                    'payment_method_id' => $pmModels[array_rand($pmModels)]->id,
                    'category' => 'Serviços',
                    'client_id' => $client->id,
                    'user_id' => $user->id,
                    'created_at' => $date,
                ]);
            }
        }

        // 6. Extra Expenses
        $this->command->info("Criando despesas...");
        $categories = ['Aluguel', 'Energia', 'Água', 'Internet', 'Peças'];
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays(rand(1, 180));
            $isPaid = rand(0, 10) > 3;

            FinancialTransaction::create([
                'company_id' => $company->id,
                'description' => $categories[array_rand($categories)],
                'amount' => rand(50, 1500),
                'type' => 'out',
                'status' => $isPaid ? 'paid' : 'pending',
                'due_date' => $date,
                'paid_at' => $isPaid ? $date : null,
                'payment_method_id' => $pmModels[array_rand($pmModels)]->id,
                'category' => 'Operacional',
                'supplier_id' => $suppliers[array_rand($suppliers)]->id,
                'user_id' => $user->id,
                'created_at' => $date,
            ]);
        }

        // 7. Appointments
        $this->command->info("Agendamentos...");
        for ($i = 0; $i < 20; $i++) {
            $date = Carbon::now()->addDays(rand(-10, 20));
            Appointment::create([
                'company_id' => $company->id,
                'customer_name' => 'Cliente Agendado ' . $i,
                'customer_phone' => '(11) 91234-' . rand(1000, 9999),
                'vehicle_plate' => 'ABC' . rand(1000, 9999),
                'service_type' => 'Revisão Geral',
                'scheduled_at' => $date,
                'status' => 'scheduled',
                'token' => (string) Str::uuid(),
            ]);
        }

        $this->command->info("Finalizado!");
    }
}
