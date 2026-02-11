<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Clients;
use App\Models\Vehicles;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or Get User
        $user = User::firstOrCreate(
            ['email' => 'grafit933@gmail.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'company_id' => 1, // Assuming company ID 1 exists or is nullable/default
                'niche' => 'automotive',
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $companyId = $user->company_id ?? 1;

        // 2. Create Client
        $client = Clients::firstOrCreate(
            ['cpf' => '123.456.789-00'],
            [
                'company_id' => $companyId,
                'name' => 'João Silva',
                'email' => 'joao.cliente@exemplo.com',
                'phone' => '(11) 99999-8888',
                'type' => 'PF',
                'uuid' => (string) Str::uuid(),
            ]
        );

        // 3. Create Vehicle
        $vehicle = Vehicles::firstOrCreate(
            ['placa' => 'ABC-1234'],
            [
                'company_id' => $companyId,
                'cliente_id' => $client->id,
                'marca' => 'Chevrolet',
                'modelo' => 'Onix LTZ',
                'ano_modelo' => 2023,
                'ano_fabricacao' => 2023,
                'cor' => 'Preto',
                'combustivel' => 'Flex',
                'km_atual' => 15000,
                'ativo' => true,
            ]
        );

        // 4. Create Service Orders (OS)

        // OS 1: Pendente
        OrdemServico::create([
            'company_id' => $companyId,
            'client_id' => $client->id,
            'veiculo_id' => $vehicle->id,
            'user_id' => $user->id,
            'status' => 'pending', // or 'bet_pending' based on your enum
            'description' => 'Revisão de 15.000km e troca de óleo.',
            'km_entry' => 15000,
            'scheduled_at' => now()->addDays(2),
        ]);

        // OS 2: Em Andamento
        OrdemServico::create([
            'company_id' => $companyId,
            'client_id' => $client->id,
            'veiculo_id' => $vehicle->id,
            'user_id' => $user->id,
            'status' => 'in_progress', // or 'approved'
            'description' => 'Troca de pastilhas de freio dianteiras.',
            'km_entry' => 15050,
            'scheduled_at' => now(),
        ]);

        // OS 3: Concluída
        OrdemServico::create([
            'company_id' => $companyId,
            'client_id' => $client->id,
            'veiculo_id' => $vehicle->id,
            'user_id' => $user->id,
            'status' => 'completed', // or 'finished'
            'description' => 'Alinhamento e balanceamento.',
            'km_entry' => 14000,
            'scheduled_at' => now()->subDays(5),
        ]);

        $this->command->info('Dados de demonstração criados para grafit933@gmail.com com sucesso!');
    }
}
