<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Niche Configuration
    |--------------------------------------------------------------------------
    |
    | This file controls the active niche of the application.
    | Changing the 'current' value will update labels, validation rules,
    | and features across the system.
    |
    | Supported niches: 'automotive', 'pet', 'clinic', 'electronics'
    |
    */

    'current' => env('APP_NICHE', 'automotive'),

    'niches' => [
        'automotive' => [
            'labels' => [
                'entity' => 'Veículo',
                'entities' => 'Veículos',
                'new_entity' => 'Novo Veículo',
                'identifier' => 'Placa',
                'secondary_identifier' => 'Renavam',
                'metric' => 'KM',
                'metric_unit' => 'km',
                'fuel' => 'Combustível',
                'fuel_levels' => ['Reserva', '1/4', '1/2', '3/4', 'Cheio'],
                'visual_inspection_title' => 'Vistoria Visual (Tira-Teima)',
                'visual_inspection_help' => 'Clique na imagem para marcar avarias.',
                'visual_inspection_prompt_title' => 'Descreva a avaria',
                'visual_inspection_prompt_placeholder' => 'Ex: Risco, Amassado...',
                'brand' => 'Marca',
                'model' => 'Modelo',
                'color' => 'Cor',
                'year' => 'Ano',
                'features' => 'Opcionais',
                'inventory_items' => 'Itens/Peças',
                'checklist_categories' => [
                    'Motor' => ['Óleo do Motor', 'Filtro de Ar', 'Correia Dentada', 'Velas', 'Sistema de Arrefecimento'],
                    'Freios' => ['Pastilhas Dianteiras', 'Discos Dianteiros', 'Lona Traseira', 'Fluido de Freio'],
                    'Suspensão' => ['Amortecedores', 'Buchas', 'Pneus', 'Alinhamento'],
                    'Elétrica' => ['Bateria', 'Alternador', 'Lâmpadas', 'Motor de Arranque']
                ],
            ],
            'icons' => [
                'entity' => 'ti-car',
                'identifier' => 'ti-id',
            ],
            'components' => [
                'visual_inspection' => 'components.visual-inspection.automotive',
            ]
        ],
        'electronics' => [
            'labels' => [
                'entity' => 'Dispositivo',
                'entities' => 'Dispositivos',
                'new_entity' => 'Novo Dispositivo',
                'identifier' => 'Número de Série',
                'secondary_identifier' => 'Modelo',
                'metric' => 'Ciclos de Bateria',
                'metric_unit' => 'ciclos',
                'fuel' => 'Carga',
                'fuel_levels' => ['0%', '25%', '50%', '75%', '100%'],
                'visual_inspection_title' => 'Inspeção Física',
                'visual_inspection_help' => 'Clique no dispositivo para marcar riscos ou danos.',
                'brand' => 'Fabricante',
                'model' => 'Modelo',
                'color' => 'Cor',
                'year' => 'Ano de Fabricação',
                'features' => 'Especificações',
                'checklist_categories' => [
                    'Tela / Display' => ['Riscos', 'Trincas', 'Touchscreen', 'Manchas', 'Dead Pixels'],
                    'Carcaça' => ['Amassados', 'Riscos', 'Parafusos Faltando', 'Dobradiças'],
                    'Bateria / Energia' => ['Saúde da Bateria', 'Conector de Carga', 'Carregador Original', 'Cabo USB'],
                    'Funcional' => ['Wi-Fi / Bluetooth', 'Câmeras', 'Microfone', 'Alto-falantes', 'Botões Físicos']
                ],
            ],
            'icons' => [
                'entity' => 'ti-device-laptop',
                'identifier' => 'ti-barcode',
            ],
            'components' => [
                'visual_inspection' => 'components.visual-inspection.electronics',
            ]
        ],
        // Add more niches here...
        'pet' => [
            'labels' => [
                'entity' => 'Pet',
                'entities' => 'Pets',
                'new_entity' => 'Novo Pet',
                'identifier' => 'Nome do Pet',
                'secondary_identifier' => 'Raça',
                'metric' => 'Idade',
                'metric_unit' => 'anos',
                'fuel' => 'Porte',
                'fuel_levels' => ['Pequeno', 'Médio', 'Grande', 'Gigante'],
                'visual_inspection_title' => 'Avaliação Física',
                'visual_inspection_help' => 'Marque pontos de atenção na saúde do pet.',
                'brand' => 'Espécie',
                'model' => 'Raça',
                'color' => 'Pelagem',
                'year' => 'Data de Nascimento',
                'features' => 'Alergias / Cuidados',
                'inventory_items' => 'Insumos/Produtos',
                'checklist_categories' => [
                    'Exame Físico' => ['Mucosas', 'Hidratação', 'Temperatura', 'Batimentos Cardíacos', 'Respiração'],
                    'Pele e Pelagem' => ['Queda de Pelo', 'Feridas', 'Parasitas (Pulgas/Carrapatos)', 'Nódulos', 'Otite'],
                    'Higiene' => ['Unhas', 'Limpeza de Ouvidos', 'Glândulas Anais', 'Dentes / Tártaro'],
                    'Comportamento' => ['Agressividade', 'Medo / Ansiedade', 'Sociabilidade', 'Apetite']
                ],
            ],
            'icons' => [
                'entity' => 'ti-dog',
                'identifier' => 'ti-paws',
            ],
        ],
        'beauty_clinic' => [
            'labels' => [
                'entity' => 'Paciente',
                'entities' => 'Pacientes',
                'new_entity' => 'Novo Paciente',
                'identifier' => 'Nome Completo',
                'secondary_identifier' => 'CPF',
                'metric' => 'Sessões',
                'metric_unit' => 'sessões',
                'fuel' => 'Tipo de Pele',
                'fuel_levels' => ['Seca', 'Mista', 'Oleosa', 'Sensível'],
                'visual_inspection_title' => 'Mapa Facial/Corporal',
                'visual_inspection_help' => 'Marque áreas de tratamento no mapa.',
                'brand' => 'Gênero',
                'model' => 'Protocolo',
                'color' => 'Plano de Tratamento',
                'year' => 'Última Visita',
                'features' => 'Observações Clínicas',
                'inventory_items' => 'Insumos / Dermocosméticos',
                'checklist_categories' => [
                    'Anamnese Facial' => ['Tipo de Pele', 'Manchas / Melasma', 'Acne / Cicatrizes', 'Rugas / Linhas', 'Flacidez'],
                    'Anamnese Corporal' => ['Gordura Localizada', 'Celulite', 'Estrias', 'Flacidez Corporal', 'Retenção Hídrica'],
                    'Histórico' => ['Alergias', 'Medicamentos em Uso', 'Procedimentos Anteriores', 'Gestante / Lactante'],
                    'Hábitos' => ['Ingestão de Água', 'Alimentação', 'Atividade Física', 'Exposição Solar', 'Tabagismo']
                ],
            ],
            'icons' => [
                'entity' => 'ti-user-heart',
                'identifier' => 'ti-id',
            ],
        ],
    ],
];
