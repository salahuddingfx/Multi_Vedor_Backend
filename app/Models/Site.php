<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['name', 'slug', 'settings'];
    protected $casts = ['settings' => 'array'];

    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function categories(): HasMany { return $this->hasMany(Category::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function heroSlides(): HasMany { return $this->hasMany(HeroSlide::class); }
}
