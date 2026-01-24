<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Clients;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // PF Client
        Clients::create([
            'type' => 'PF',
            'name' => 'João da Silva',
            'cpf' => '123.456.789-00',
            'email' => 'joao@email.com',
            'phone' => '(11) 99999-1234',
            'status' => 'Ativo',
            'country' => 'Brasil'
        ]);

        // PJ Client
        Clients::create([
            'type' => 'PJ',
            'company_name' => 'Empresa Tech Ltda',
            'trade_name' => 'Tech Solutions',
            'cnpj' => '12.345.678/0001-90',
            'email' => 'contato@techsolutions.com',
            'phone' => '(11) 3333-4444',
            'status' => 'Ativo',
            'country' => 'Brasil'
        ]);

        // Another PF
        Clients::create([
            'type' => 'PF',
            'name' => 'Maria Oliveira',
            'cpf' => '987.654.321-11',
            'email' => 'maria@email.com',
            'phone' => '(21) 98888-5678',
            'status' => 'Inativo',
            'country' => 'Brasil'
        ]);
    }
}
