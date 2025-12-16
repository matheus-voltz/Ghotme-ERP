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

            /*
            |--------------------------------------------------------------------------
            | 👤 CLIENTE – CAMPOS BASE (TODOS OS SEGMENTOS)
            |--------------------------------------------------------------------------
            */

            // 📞 CONTATO
            [
                'segment' => 'cliente',
                'label' => 'Telefone',
                'field_key' => 'telefone',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-phone',
                'order' => 1,
                'active' => true,
            ],
            [
                'segment' => 'cliente',
                'label' => 'WhatsApp',
                'field_key' => 'whatsapp',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-brand-whatsapp',
                'order' => 2,
                'active' => true,
            ],
            [
                'segment' => 'cliente',
                'label' => 'E-mail',
                'field_key' => 'email',
                'field_type' => 'email',
                'required' => false,
                'icon' => 'ti tabler-mail',
                'order' => 3,
                'active' => true,
            ],

            // 📍 ENDEREÇO
            [
                'segment' => 'cliente',
                'label' => 'CEP',
                'field_key' => 'cep',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-map-pin',
                'order' => 4,
                'active' => true,
            ],
            [
                'segment' => 'cliente',
                'label' => 'Cidade',
                'field_key' => 'cidade',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-building-community',
                'order' => 5,
                'active' => true,
            ],
            [
                'segment' => 'cliente',
                'label' => 'Estado',
                'field_key' => 'estado',
                'field_type' => 'select',
                'options' => [
                    'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS',
                    'MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC',
                    'SP','SE','TO'
                ],
                'required' => false,
                'icon' => 'ti tabler-map',
                'order' => 6,
                'active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | 🔧 OFICINA MECÂNICA (VEÍCULO)
            |--------------------------------------------------------------------------
            */

            [
                'segment' => 'oficina',
                'label' => 'Placa do Veículo',
                'field_key' => 'placa',
                'field_type' => 'text',
                'required' => true,
                'icon' => 'ti tabler-car',
                'order' => 1,
                'active' => true,
            ],
            [
                'segment' => 'oficina',
                'label' => 'Marca',
                'field_key' => 'marca',
                'field_type' => 'text',
                'required' => true,
                'icon' => 'ti tabler-tag',
                'order' => 2,
                'active' => true,
            ],
            [
                'segment' => 'oficina',
                'label' => 'Modelo',
                'field_key' => 'modelo',
                'field_type' => 'text',
                'required' => true,
                'icon' => 'ti tabler-car-garage',
                'order' => 3,
                'active' => true,
            ],
            [
                'segment' => 'oficina',
                'label' => 'Ano',
                'field_key' => 'ano',
                'field_type' => 'number',
                'required' => false,
                'icon' => 'ti tabler-calendar',
                'order' => 4,
                'active' => true,
            ],
            [
                'segment' => 'oficina',
                'label' => 'Tipo de Combustível',
                'field_key' => 'combustivel',
                'field_type' => 'select',
                'options' => ['Gasolina', 'Etanol', 'Diesel', 'Flex', 'Elétrico'],
                'required' => false,
                'icon' => 'ti tabler-gas-station',
                'order' => 5,
                'active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | 🏥 CLÍNICA
            |--------------------------------------------------------------------------
            */

            [
                'segment' => 'clinica',
                'label' => 'Convênio',
                'field_key' => 'convenio',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-heart',
                'order' => 1,
                'active' => true,
            ],
            [
                'segment' => 'clinica',
                'label' => 'Número da Carteirinha',
                'field_key' => 'carteirinha',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-id',
                'order' => 2,
                'active' => true,
            ],
            [
                'segment' => 'clinica',
                'label' => 'Plano',
                'field_key' => 'plano',
                'field_type' => 'select',
                'options' => ['Básico', 'Intermediário', 'Premium'],
                'required' => false,
                'icon' => 'ti tabler-list',
                'order' => 3,
                'active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | 🏢 EMPRESA / SERVIÇOS
            |--------------------------------------------------------------------------
            */

            [
                'segment' => 'empresa',
                'label' => 'Ramo de Atividade',
                'field_key' => 'ramo',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-briefcase',
                'order' => 1,
                'active' => true,
            ],
            [
                'segment' => 'empresa',
                'label' => 'Número de Funcionários',
                'field_key' => 'funcionarios',
                'field_type' => 'number',
                'required' => false,
                'icon' => 'ti tabler-users',
                'order' => 2,
                'active' => true,
            ],
            [
                'segment' => 'empresa',
                'label' => 'Faturamento Médio',
                'field_key' => 'faturamento',
                'field_type' => 'text',
                'required' => false,
                'icon' => 'ti tabler-currency-dollar',
                'order' => 3,
                'active' => true,
            ],
        ];

        foreach ($fields as $field) {
            ClientField::updateOrCreate(
                ['segment' => $field['segment'], 'field_key' => $field['field_key']],
                $field
            );
        }
    }
}
