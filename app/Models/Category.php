<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['site_id', 'name', 'slug', 'is_featured', 'image_path'];

    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
}
