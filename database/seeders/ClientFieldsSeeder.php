<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClientField;

class ClientFieldsSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [

            // ======================
            // 🔧 OFICINA MECÂNICA
            // ======================
            [
                'segment' => 'oficina',
                'label' => 'Placa do Veículo',
                'field_key' => 'placa',
                'field_type' => 'text',
                'required' => true,
                'icon' => 'ti tabler-car',
                'order' => 1
            ],
            [
                'segment' => 'oficina',
                'label' => 'Marca',
                'field_key' => 'marca',
                'field_type' => 'text',
                'required' => true,
                'icon' => 'ti tabler-tag',
                'order' => 2
            ],
            [
                'segment' => 'oficina',
                'label' => 'Modelo',
                'field_key' => 'modelo',
                'field_type' => 'text',
                'required' => true,
                'icon' => 'ti tabler-car-garage',
                'order' => 3
            ],
            [
                'segment' => 'oficina',
                'label' => 'Ano',
                'field_key' => 'ano',
                'field_type' => 'number',
                'required' => false,
                'icon' => 'ti tabler-calendar',
                'order' => 4
            ],
            [
                'segment' => 'oficina',
                'label' => 'Tipo de Combustível',
                'field_key' => 'combustivel',
                'field_type' => 'select',
                'options' => json_encode(['Gasolina', 'Etanol', 'Diesel', 'Flex', 'Elétrico']),
                'required' => false,
                'icon' => 'ti tabler-gas-station',
                'order' => 5
            ],

            // ======================
            // 🏥 CLÍNICA
            // ======================
            [
                'segment' => 'clinica',
                'label' => 'Convênio',
                'field_key' => 'convenio',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-heart',
                'order' => 1
            ],
            [
                'segment' => 'clinica',
                'label' => 'Número da Carteirinha',
                'field_key' => 'carteirinha',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-id',
                'order' => 2
            ],
            [
                'segment' => 'clinica',
                'label' => 'Plano',
                'field_key' => 'plano',
                'field_type' => 'select',
                'options' => json_encode(['Básico', 'Intermediário', 'Premium']),
                'required' => false,
                'icon' => 'ti tabler-list',
                'order' => 3
            ],

            // ======================
            // 🏢 EMPRESA / SERVIÇOS
            // ======================
            [
                'segment' => 'empresa',
                'label' => 'Ramo de Atividade',
                'field_key' => 'ramo',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-briefcase',
                'order' => 1
            ],
            [
                'segment' => 'empresa',
                'label' => 'Número de Funcionários',
                'field_key' => 'funcionarios',
                'field_type' => 'number',
                'required' => false,
                'icon' => 'ti tabler-users',
                'order' => 2
            ],
            [
                'segment' => 'empresa',
                'label' => 'Faturamento Médio',
                'field_key' => 'faturamento',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-currency-dollar',
                'order' => 3
            ],
        ];

        foreach ($fields as $field) {
            ClientField::create($field);
        }
    }
}
