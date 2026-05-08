<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function index()
    {
        return response()->json(Coupon::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:coupons',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
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
        $coupon->delete();
        return response()->json(null, 204);
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'items' => 'nullable|array' // array of objects { product_id, quantity }
        ]);
        
        $coupon = Coupon::where('code', $request->code)->with('products')->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid coupon code'], 404);
        }
        
        if (!$coupon->isValid()) {
            return response()->json(['message' => 'Coupon has expired or is inactive'], 400);
        }

        // Check product restrictions
        if ($coupon->products->count() > 0) {
            $cartProductIds = collect($request->items)->pluck('product_id')->toArray();
            $restrictedProductIds = $coupon->products->pluck('id')->toArray();
            
            // Check if ANY item in the cart is eligible
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
