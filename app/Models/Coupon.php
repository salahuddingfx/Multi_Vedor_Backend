<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'site_id',
        'max_uses',
        'per_user_limit',
        'first_order_only',
        'is_active',
        'expires_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
        'expires_at' => 'datetime'
    ];

    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function hasReachedMaxUses(): bool
    {
        if ($this->max_uses === null) return false;

        return $this->usageCount() >= $this->max_uses;
    }

    public function hasReachedUserLimit(string $phone): bool
    {
        if ($this->per_user_limit === null) return false;

        return $this->userUsageCount($phone) >= $this->per_user_limit;
    }

    public function isFirstOrderOnlyEligible(string $phone): bool
    {
        if (!$this->first_order_only) return true;

        $query = Order::where('customer_phone', $phone);

        if ($this->site_id) {
            $query->where('site_id', $this->site_id);
        }

        return $query->doesntExist();
    }

    public function usageCount(): int
    {
        return $this->usages()->count();
    }

    public function userUsageCount(string $phone): int
    {
        return $this->usages()->where('customer_phone', $phone)->count();
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}
