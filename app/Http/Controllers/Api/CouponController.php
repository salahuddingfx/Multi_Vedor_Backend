<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::withCount('usages')->latest();

        if ($request->site_id) {
            $query->where('site_id', $request->site_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:coupons',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'site_id' => 'nullable|exists:sites,id',
            'max_uses' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'first_order_only' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $coupon = Coupon::create($validated);
        
        if (isset($validated['product_ids'])) {
            $coupon->products()->sync($validated['product_ids']);
        }

        return response()->json($coupon->load('products'), 201);
    }

    public function show(Coupon $coupon)
    {
        return response()->json($coupon->load('products'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'site_id' => 'nullable|exists:sites,id',
            'max_uses' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'first_order_only' => 'boolean',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $coupon->update($validated);

        if (isset($validated['product_ids'])) {
            $coupon->products()->sync($validated['product_ids']);
        } else {
            $coupon->products()->detach();
        }

        return response()->json($coupon->load('products'));
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->products()->detach();
        $coupon->usages()->delete();
        $coupon->delete();
        return response()->json(null, 204);
    }

    public function validateCoupon(Request $request, $site_slug)
    {
        $request->validate([
            'code' => 'required',
            'customer_phone' => 'nullable|string',
            'items' => 'nullable|array'
        ]);
        
        $coupon = Coupon::where('code', $request->code)->with('products')->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid coupon code'], 404);
        }

        // Scope by site if coupon is restricted to a specific site
        $site = \App\Models\Site::where('slug', $site_slug)->first();
        if (!$site) {
            return response()->json(['message' => 'Site not found'], 404);
        }
        if ($coupon->site_id && $coupon->site_id !== $site->id) {
            return response()->json(['message' => 'Invalid coupon code'], 404);
        }
        
        if (!$coupon->isValid()) {
            return response()->json(['message' => 'Coupon has expired or is inactive'], 400);
        }

        if ($coupon->hasReachedMaxUses()) {
            return response()->json(['message' => 'This coupon has reached its usage limit'], 400);
        }

        if ($request->customer_phone) {
            if ($coupon->hasReachedUserLimit($request->customer_phone)) {
                return response()->json(['message' => 'You have already used this coupon'], 400);
            }

            if (!$coupon->isFirstOrderOnlyEligible($request->customer_phone)) {
                return response()->json(['message' => 'This coupon is for first-time customers only'], 400);
            }
        }

        // Check product restrictions
        if ($coupon->products->count() > 0) {
            $cartProductIds = collect($request->items)->pluck('product_id')->toArray();
            $restrictedProductIds = $coupon->products->pluck('id')->toArray();
            
            $eligibleItems = array_intersect($cartProductIds, $restrictedProductIds);
            
            if (empty($eligibleItems)) {
                return response()->json([
                    'message' => 'This coupon is not applicable to the products in your cart.'
                ], 400);
            }
        }

        return response()->json([
            'message' => 'Coupon applied successfully',
            'coupon' => $coupon
        ]);
    }
}
