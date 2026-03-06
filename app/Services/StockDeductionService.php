<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\InventoryItem;
use App\Models\RecipeItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockDeductionService
{
    public function deductForOrder(OrdemServico $os): void
    {
        $os->loadMissing('parts');

        if ($os->parts->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($os) {
            foreach ($os->parts as $part) {
                $recipeItems = RecipeItem::where('inventory_item_id', $part->inventory_item_id)->get();

                if ($recipeItems->isNotEmpty()) {
                    $this->deductIngredients($recipeItems, $part->quantity, $os->id);
                } else {
                    $this->deductItem($part->inventory_item_id, $part->quantity, $os->id);
                }
            }
        });
    }

    private function deductIngredients($recipeItems, int $productQty, int $osId): void
    {
        foreach ($recipeItems as $recipe) {
            $totalQty = $recipe->quantity * $productQty;
            $this->deductItem($recipe->ingredient_id, $totalQty, $osId);
        }
    }

    private function deductItem(int $itemId, float $qty, int $osId): void
    {
        $item = InventoryItem::lockForUpdate()->find($itemId);

        if (!$item) {
            return;
        }

        $item->quantity = max(0, $item->quantity - $qty);
        $item->save();

        StockMovement::create([
            'inventory_item_id' => $itemId,
            'type' => 'out',
            'quantity' => (int) ceil($qty),
            'unit_price' => $item->cost_price,
            'reason' => "Baixa automática - Pedido #{$osId}",
            'user_id' => Auth::id(),
        ]);
    }
}
