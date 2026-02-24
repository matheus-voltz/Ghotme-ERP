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
    | Supported niches: 'automotive', 'pet', 'beauty_clinic', 'electronics', 'construction'
    |
    */

    'current' => env('APP_NICHE', 'automotive'),

    'niches' => [
        'workshop' => [
            'labels' => [
                'entity' => 'Veículo',
                'entities' => 'Veículos',
                'url_slug' => 'veiculo',
                'url_entities_slug' => 'veiculos',
                'url_client_slug' => 'cliente',
                'url_clients_slug' => 'clientes',
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
                'in_service_label' => 'Na Oficina',
                'timeline_checkin_body' => 'O seu veículo deu entrada com sucesso na oficina.',
                'timeline_execution_body' => 'Nossos técnicos estão trabalhando no seu veículo neste momento.',
                'timeline_finalizing_body' => 'Realizamos os últimos ajustes e testes para garantir sua segurança total.',
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
        'automotive' => [
            'labels' => [
                'entity' => 'Veículo',
                'entities' => 'Veículos',
                'url_slug' => 'veiculo',
                'url_entities_slug' => 'veiculos',
                'url_client_slug' => 'cliente',
                'url_clients_slug' => 'clientes',
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
                'in_service_label' => 'Na Oficina',
                'timeline_checkin_body' => 'O seu veículo deu entrada com sucesso na oficina.',
                'timeline_execution_body' => 'Nossos técnicos estão trabalhando no seu veículo neste momento.',
                'timeline_finalizing_body' => 'Realizamos os últimos ajustes e testes para garantir sua segurança total.',
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
                'url_slug' => 'dispositivo',
                'url_entities_slug' => 'dispositivos',
                'url_client_slug' => 'cliente',
                'url_clients_slug' => 'clientes',
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
                'in_service_label' => 'Em Manutenção',
                'timeline_checkin_body' => 'O seu dispositivo deu entrada com sucesso na bancada.',
                'timeline_execution_body' => 'Nossos técnicos estão trabalhando no seu dispositivo neste momento.',
                'timeline_finalizing_body' => 'Estamos realizando os testes finais para garantir o pleno funcionamento.',
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
                'url_slug' => 'pet',
                'url_entities_slug' => 'pets',
                'url_client_slug' => 'cliente',
                'url_clients_slug' => 'clientes',
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
                'in_service_label' => 'Em Atendimento',
                'timeline_checkin_body' => 'Seu pet já está sob nossos cuidados e pronto para o atendimento.',
                'timeline_execution_body' => 'Nossos profissionais estão cuidando do seu pet com toda atenção e carinho.',
                'timeline_finalizing_body' => 'Estamos finalizando os últimos cuidados e observações pós-atendimento.',
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
                'url_slug' => 'paciente',
                'url_entities_slug' => 'pacientes',
                'url_client_slug' => 'cliente',
                'url_clients_slug' => 'clientes',
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
                'in_service_label' => 'Em Procedimento',
                'timeline_checkin_body' => 'O check-in foi realizado e já estamos preparando tudo para o procedimento.',
                'timeline_execution_body' => 'O seu procedimento está sendo realizado por nossos especialistas.',
                'timeline_finalizing_body' => 'Estamos finalizando com os últimos cuidados e orientações pós-procedimento.',
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
        'construction' => [
            'labels' => [
                'entity' => 'Obra',
                'entities' => 'Obras',
                'url_slug' => 'obra',
                'url_entities_slug' => 'obras',
                'url_client_slug' => 'cliente',
                'url_clients_slug' => 'clientes',
                'new_entity' => 'Nova Obra',
                'identifier' => 'ART / RRT',
                'secondary_identifier' => 'Cód. Obra',
                'metric' => 'Progresso',
                'metric_unit' => '%',
                'fuel' => 'Clima',
                'fuel_levels' => ['Bom', 'Nublado', 'Chuvoso', 'Impraticável'],
                'visual_inspection_title' => 'Relatório Fotográfico',
                'visual_inspection_help' => 'Clique na planta para marcar pontos de atenção ou ocorrências.',
                'brand' => 'Tipo de Obra',
                'model' => 'Projeto',
                'color' => 'Status',
                'year' => 'Previsão de Entrega',
                'features' => 'Responsáveis / Engenheiros',
                'inventory_items' => 'Materiais / Equipamentos',
                'in_service_label' => 'Em Execução',
                'timeline_checkin_body' => 'Início das atividades registrado com sucesso no RDO.',
                'timeline_execution_body' => 'As equipes estão em campo executando as atividades programadas.',
                'timeline_finalizing_body' => 'Atividades do dia concluídas e relatório de progresso gerado.',
                'checklist_categories' => [
                    'Efetivo (Mão de Obra)' => ['Mestre de Obra', 'Pedreiros', 'Serventes', 'Eletricistas', 'Encanadores'],
                    'Segurança (EPI/EPC)' => ['Uso de Capacete', 'Botas de Segurança', 'Sinalização de Área', 'Extintores', 'Cintos de Segurança'],
                    'Equipamentos' => ['Betoneira', 'Andaimes', 'Furadeiras/Marteletes', 'Escadas', 'Container/Almoxarifado'],
                    'Infraestrutura' => ['Limpeza do Canteiro', 'Ligação Provisória Luz', 'Ligação Provisória Água', 'Banheiros Químicos']
                ],
            ],
            'icons' => [
                'entity' => 'ti-building-skyscraper',
                'identifier' => 'ti-file-certificate',
            ],
            'components' => [
                'visual_inspection' => 'components.visual-inspection.construction',
            ]
        ],
    ],
];
