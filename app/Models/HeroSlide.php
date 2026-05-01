<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = ['site_id', 'title', 'subtitle', 'badge', 'image_path', 'order'];
}
