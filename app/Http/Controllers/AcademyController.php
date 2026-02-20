<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AcademyController extends Controller
{
    /**
     * Retorna a lista de tutoriais (pode vir de um DB no futuro)
     */
    public function index(Request $request)
    {
        $tutorials = [
            [
                'title' => 'Como criar uma Ordem de Serviço',
                'category' => 'Ordens de Serviço',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', // Exemplo
                'description' => 'Aprenda a abrir uma OS em menos de 1 minuto.',
                'tags' => 'os, serviço, abrir, novo'
            ],
            [
                'title' => 'Como fechar o caixa e conciliar',
                'category' => 'Financeiro',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'description' => 'Guia rápido sobre fluxo de caixa e conciliação bancária.',
                'tags' => 'caixa, fechar, financeiro, banco'
            ],
            [
                'title' => 'Cadastrando seu primeiro cliente',
                'category' => 'CRM',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'description' => 'Importação e cadastro manual de clientes.',
                'tags' => 'cliente, cadastrar, crm'
            ],
            [
                'title' => 'Configurando Campos Personalizados',
                'category' => 'Configurações',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'description' => 'Como adaptar o sistema ao seu nicho.',
                'tags' => 'custom, campos, nicho'
            ]
        ];

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $tutorials = array_filter($tutorials, function($item) use ($search) {
                return str_contains(strtolower($item['title']), $search) || 
                       str_contains(strtolower($item['tags']), $search);
            });
        }

        return response()->json(array_values($tutorials));
    }
}
