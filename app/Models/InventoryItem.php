<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToCompany;

class InventoryItem extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'sku',
        'description',
        'cost_price',
        'selling_price',
        'quantity',
        'min_quantity',
        'supplier_id',
        'unit',
        'location',
        'is_active',
        'menu_category_id',
        'is_ingredient',
        'expiry_date',
        'batch_number',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    /**
     * Se for um produto final (Hot Dog), retorna os ingredientes dele
     */
    public function ingredients()
    {
        return $this->hasMany(ProductRecipe::class, 'product_id');
    }

    /**
     * Se for um insumo (Salsicha), retorna em quais lanches ele é usado
     */
    public function asIngredientIn()
    {
        return $this->hasMany(ProductRecipe::class, 'ingredient_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('order');
    }

    public function mainImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('is_main', true);
    }

    public function marketplacePublications()
    {
        return $this->morphMany(MarketplacePublication::class, 'publishable');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recipe()
    {
        return $this->hasMany(RecipeItem::class, 'inventory_item_id');
    }

    public function usedInRecipes()
    {
        return $this->hasMany(RecipeItem::class, 'ingredient_id');
    }
}