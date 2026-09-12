<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id', 'name', 'sku', 'description', 'image_path',
    'price', 'wholesale_price', 'unit', 'stock_quantity', 'min_stock_alert', 'is_active',
])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockStatus(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out';
        }

        return $this->stock_quantity <= $this->min_stock_alert ? 'low' : 'in';
    }
}
