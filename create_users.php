<?php

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

// 1. Criar Empresa de Teste
$company = Company::firstOrCreate(
    ['name' => 'Oficina do Zé'],
    [
        'email' => 'ze@oficina.com',
        'document_number' => '12345678000199',
        'address' => 'Rua das Oficinas, 100',
        'phone' => '41999999999'
    ]
);

echo "Empresa criada: " . $company->name . "
";

// 2. Criar Usuário Suporte (Matheus) - Sem Company ID (Global)
$admin = User::updateOrCreate(
    ['email' => 'suporte@ghotme.com'],
    [
        'name' => 'Matheus Ghotme',
        'password' => Hash::make('password'),
        'company_id' => null, // Importante: NULL para ser suporte global
        'role' => 'super_admin' // Se tiver essa coluna
    ]
);
echo "Admin criado: " . $admin->email . "
";

// 3. Criar Dono da Oficina (Zé)
$dono = User::updateOrCreate(
    ['email' => 'ze@oficina.com'],
    [
        'name' => 'Zé Mecânico',
        'password' => Hash::make('password'),
        'company_id' => $company->id,
        'role' => 'admin'
    ]
);
echo "Dono criado: " . $dono->email . "
";

// 4. Criar Funcionário (Tiago)
$func = User::updateOrCreate(
    ['email' => 'tiago@oficina.com'],
    [
        'name' => 'Tiago Ajudante',
        'password' => Hash::make('password'),
        'company_id' => $company->id,
        'role' => 'employee'
    ]
);
echo "Funcionário criado: " . $func->email . "
";
