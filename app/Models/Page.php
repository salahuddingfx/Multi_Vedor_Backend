<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['site_id', 'title', 'slug', 'content', 'is_active'];

    public function site() {
        return $this->belongsTo(Site::class);
    }
}
