<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_taxable' => 'boolean',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_on_hand' => 'decimal:3',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'sku', 'price', 'stock_on_hand'])
            ->logOnlyDirty();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // WMS Relations
    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function getStockOnHandAttribute($value)
    {
        return $value ?? 0;
    }

    /**
     * Synchronize the denormalized stock_on_hand field with inventory_stocks.
     */
    public function refreshStockOnHand(): void
    {
        $total = $this->stocks()->sum('quantity');
        $this->update(['stock_on_hand' => $total]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getImageUrlAttribute()
    {
        $image = $this->images()->where('is_primary', true)->first() ?? $this->images()->first();
        
        if ($image) {
            return asset('storage/' . $image->image_path);
        }

        // Return a premium looking category-based placeholder
        $category = $this->category->name ?? 'Product';
        return "https://images.unsplash.com/photo-1592910710242-793263bba68c?q=80&w=400&auto=format&fit=crop"; // High quality farm/agri image as default
    }

    public function getPriceListPriceAttribute()
    {
        return $this->price;
    }
}
