<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\BudgetPart;
use App\Models\Clients;
use App\Models\Company;
use App\Models\InventoryItem;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BudgetApprovalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar uma Empresa de Teste
        $company = Company::create([
            'name' => 'Oficina Master Car',
            'email' => 'contato@mastercar.com',
            'phone' => '(11) 99999-9999',
            'address' => 'Rua das Oficinas, 123',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);

        // 2. Criar um Usuário para esta empresa
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Mecânico Chefe',
            'email' => 'mecanico@teste.com',
            'password' => Hash::make('senha123'),
            'email_verified_at' => now(),
        ]);

        // 3. Criar um Cliente
        $client = Clients::create([
            'company_id' => $company->id,
            'type' => 'PF',
            'name' => 'João da Silva',
            'email' => 'joao@cliente.com',
            'phone' => '(11) 88888-8888',
            'whatsapp' => '(11) 88888-8888',
        ]);

        // 4. Criar um Veículo para o cliente
        $vehicle = Vehicles::create([
            'company_id' => $company->id,
            'cliente_id' => $client->id,
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'placa' => 'ABC-1234',
            'cor' => 'Prata',
            'ano_fabricacao' => 2022,
            'ano_modelo' => 2022,
        ]);

        // 5. Criar alguns Serviços e Peças de exemplo
        $service = Service::create([
            'company_id' => $company->id,
            'name' => 'Troca de Óleo e Filtro',
            'price' => 150.00,
        ]);

        $part = InventoryItem::create([
            'company_id' => $company->id,
            'name' => 'Filtro de Óleo Original',
            'selling_price' => 85.00,
            'quantity' => 10,
        ]);

        // 6. Criar o Orçamento Pendente
        $budget = Budget::create([
            'company_id' => $company->id,
            'uuid' => (string) Str::uuid(),
            'client_id' => $client->id,
            'veiculo_id' => $vehicle->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'valid_until' => now()->addDays(7),
            'description' => 'Revisão periódica de 20.000km conforme manual.',
        ]);

        // 7. Adicionar itens ao orçamento
        BudgetItem::create([
            'budget_id' => $budget->id,
            'service_id' => $service->id,
            'quantity' => 1,
            'price' => $service->price,
        ]);

        BudgetPart::create([
            'budget_id' => $budget->id,
            'inventory_item_id' => $part->id,
            'quantity' => 1,
            'price' => $part->selling_price,
        ]);

        $this->command->info('Ambiente de teste criado com sucesso!');
        $this->command->info('E-mail: mecanico@teste.com | Senha: senha123');
        $this->command->info('Link do Orçamento: http://localhost:8000/view-budget/' . $budget->uuid);
    }
}