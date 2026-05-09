<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['site_id', 'parent_id', 'name', 'name_bn', 'slug', 'is_featured', 'image_path'];

    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }

    public function parent(): BelongsTo { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(Category::class, 'parent_id'); }
}
