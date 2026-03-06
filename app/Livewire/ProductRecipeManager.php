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

    public function mount($productId)
    {
        $this->productId = $productId;
        $this->loadRecipe();
        $this->loadAvailableIngredients();
    }

    public function loadRecipe()
    {
        $this->ingredients = ProductRecipe::with('ingredient')
            ->where('product_id', $this->productId)
            ->get();
    }

    public function loadAvailableIngredients()
    {
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
