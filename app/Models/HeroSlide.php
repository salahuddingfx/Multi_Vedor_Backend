<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = ['site_id', 'product_id', 'title', 'subtitle', 'badge', 'button_text', 'image_path', 'order'];
}
