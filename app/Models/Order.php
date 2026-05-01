<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'site_id', 'tracking_id', 'customer_name', 'customer_phone', 
        'customer_address', 'location', 'subtotal', 'total_weight', 
        'delivery_charge', 'total_amount', 'status', 'payment_status'
    ];

    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
}
