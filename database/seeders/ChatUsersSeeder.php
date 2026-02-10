<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class ChatUsersSeeder extends Seeder
{
    public function run()
    {
        // 1. Criar Empresa de Teste
        $company = Company::firstOrCreate(
            ['name' => 'Oficina do Zé'],
            [
                'email' => 'ze@oficina.com',
                'document_number' => '12345678000199',
                'address' => 'Rua das Oficinas, 100',
                'phone' => '41999999999',
            ]
        );

        $this->command->info("Empresa criada: " . $company->name);

        // 2. Criar Usuário Suporte (Matheus) - Sem Company ID (Global)
        $admin = User::updateOrCreate(
            ['email' => 'suporte@ghotme.com'],
            [
                'name' => 'Matheus Ghotme',
                'password' => Hash::make('password'),
                'company_id' => null, // Importante: NULL para ser suporte global
                // 'role' => 'super_admin' // Comentei caso não tenha a coluna role no User
            ]
        );
        $this->command->info("Admin criado: " . $admin->email);

        // 3. Criar Dono da Oficina (Zé)
        $dono = User::updateOrCreate(
            ['email' => 'ze@oficina.com'],
            [
                'name' => 'Zé Mecânico',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                // 'role' => 'admin'
            ]
        );
        $this->command->info("Dono criado: " . $dono->email);

        // 4. Criar Funcionário (Tiago)
        $func = User::updateOrCreate(
            ['email' => 'tiago@oficina.com'],
            [
                'name' => 'Tiago Ajudante',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                // 'role' => 'employee'
            ]
        );
        $this->command->info("Funcionário criado: " . $func->email);
    }
}
