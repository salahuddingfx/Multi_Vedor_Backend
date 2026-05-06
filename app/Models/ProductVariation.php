<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id', 'weight', 'price', 'original_price', 'stock', 'sku'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
