<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'site_id',
        'product_id',
        'customer_name',
        'rating',
        'comment',
        'is_approved',
        'admin_reply',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
