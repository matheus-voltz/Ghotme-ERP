<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clients;

class ClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clients::updateOrCreate(
            ['email' => 'joao@example.com'],
            [
                'type' => 'PF',
                'name' => 'João Silva',
                'cpf' => '123.456.789-00',
                'phone' => '(11) 99999-9999',
            ]
        );

        Clients::updateOrCreate(
            ['email' => 'maria@example.com'],
            [
                'type' => 'PF',
                'name' => 'Maria Oliveira',
                'cpf' => '987.654.321-11',
                'phone' => '(11) 88888-8888',
            ]
        );

        Clients::updateOrCreate(
            ['email' => 'contato@autocenter.com'],
            [
                'type' => 'PJ',
                'company_name' => 'Auto Center Express',
                'cnpj' => '12.345.678/0001-90',
                'phone' => '(11) 3333-3333',
            ]
        );
    }
}
