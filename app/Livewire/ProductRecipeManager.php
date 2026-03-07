<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\InventoryItem;
use App\Models\ProductRecipe;
use Illuminate\Support\Facades\Auth;

class ProductRecipeManager extends Component
{
    public $productId;
    public $ingredients = [];
    public $availableIngredients = [];
    public $selectedIngredientId = '';
    public $quantity = 1;
    public $totalCost = 0;

    protected $listeners = ['load-product-recipe' => 'setProduct'];

    public function mount($productId = null)
    {
        if ($productId) {
            $this->setProduct($productId);
        }
    }

    public function setProduct($productId)
    {
        $this->productId = $productId;
        $this->loadRecipe();
        $this->loadAvailableIngredients();
        $this->calculateTotalCost();
    }

    public function loadRecipe()
    {
        if (!$this->productId) return;
        $this->ingredients = ProductRecipe::with('ingredient')
            ->where('product_id', $this->productId)
            ->get();
        $this->calculateTotalCost();
    }

    public function calculateTotalCost()
    {
        $this->totalCost = 0;
        foreach ($this->ingredients as $row) {
            $this->totalCost += $row->quantity * ($row->ingredient->cost_price ?? 0);
        }

        // Atualiza o custo no produto final no estoque
        if ($this->productId) {
            $product = InventoryItem::find($this->productId);
            if ($product && $product->cost_price != $this->totalCost) {
                $product->cost_price = $this->totalCost;
                $product->save();
            }
        }
    }

    public function loadAvailableIngredients()
    {
        if (!$this->productId) return;
        // Pega todos os itens da empresa, menos o próprio produto
        $this->availableIngredients = InventoryItem::where('company_id', Auth::user()->company_id)
            ->where('id', '!=', $this->productId)
            ->get();
    }

    public function addIngredient()
    {
        $this->validate([
            'selectedIngredientId' => 'required',
            'quantity' => 'required|numeric|min:0.001'
        ]);

        ProductRecipe::updateOrCreate(
            ['product_id' => $this->productId, 'ingredient_id' => $this->selectedIngredientId],
            ['quantity' => $this->quantity]
        );

        $this->reset(['selectedIngredientId', 'quantity']);
        $this->loadRecipe();
        $this->dispatch('recipe-updated');
    }

    public function removeIngredient($id)
    {
        ProductRecipe::find($id)->delete();
        $this->loadRecipe();
        $this->dispatch('recipe-updated');
    }

    public function render()
    {
        return view('livewire.product-recipe-manager');
    }
}
