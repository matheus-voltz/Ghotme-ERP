<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KanbanBoard;
use App\Models\KanbanItem;
use App\Models\Company;

class KanbanSeeder extends Seeder
{
    public function run()
    {
        $company = Company::first(); // Pega a primeira empresa (Oficina do Zé)

        if (!$company) return;

        $boards = [
            ['title' => 'A Fazer', 'slug' => 'todo'],
            ['title' => 'Em Progresso', 'slug' => 'in-progress'],
            ['title' => 'Feito', 'slug' => 'done'],
        ];

        foreach ($boards as $index => $b) {
            $board = KanbanBoard::firstOrCreate(
                ['company_id' => $company->id, 'slug' => $b['slug']],
                ['title' => $b['title'], 'order' => $index]
            );

            // Criar um item de exemplo no primeiro board
            if ($index === 0) {
                KanbanItem::create([
                    'kanban_board_id' => $board->id,
                    'title' => 'Revisar Freios do Honda Civic',
                    'due_date' => now()->addDays(2),
                    'badge_text' => 'Mecânica',
                    'badge_color' => 'warning',
                    'assigned_to' => ['1.png'], // Avatar fake
                ]);
            }
        }
    }
}
