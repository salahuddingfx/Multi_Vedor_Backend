<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'site_id', 'category_id', 'name', 'name_bn', 'sku', 'slug', 'description', 'description_bn',
        'price', 'original_price', 'discount_percentage', 'weight', 'stock', 'is_featured'
    ];

    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class); }
}
