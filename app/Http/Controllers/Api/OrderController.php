<?php

namespace App\Http\Controllers\Api;

use App\Models\Site;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends BaseController
{
    public function store(Request $request, $site_slug)
    {
        $site = Site::where('slug', $site_slug)->first();
        if (!$site) return $this->sendError('Site not found.');

        $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_address' => 'required|string',
            'location' => 'required|in:Cox,Outside',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request, $site) {
            $subtotal = 0;
            $totalWeight = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemPrice = $product->price * $item['quantity'];
                $itemWeight = $product->weight * $item['quantity'];

                $subtotal += $itemPrice;
                $totalWeight += $itemWeight;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'weight' => $product->weight,
                ];
            }

            // Delivery Charge Logic
            $baseCharge = ($request->location === 'Cox') ? 70 : 120;
            $deliveryCharge = $baseCharge;

            if ($totalWeight > 1.0) {
                $extraWeight = $totalWeight - 1.0;
                $extraUnits = ceil($extraWeight / 0.5);
                $deliveryCharge += ($extraUnits * 20);
            }

            $totalAmount = $subtotal + $deliveryCharge;
            $trackingId = 'ORD-' . date('Y') . '-' . strtoupper(Str::random(6));

            $order = Order::create([
                'site_id' => $site->id,
                'tracking_id' => $trackingId,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'location' => $request->location,
                'subtotal' => $subtotal,
                'total_weight' => $totalWeight,
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $totalAmount,
                'status' => 'placed',
                'payment_status' => 'unpaid',
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            return $this->sendResponse($order->load('items'), 'Order placed successfully. Tracking ID: ' . $trackingId);
        });
    }

    public function track($site_slug, $tracking_id)
    {
        $site = Site::where('slug', $site_slug)->first();
        if (!$site) return $this->sendError('Site not found.');

        $order = Order::where('site_id', $site->id)
            ->where('tracking_id', $tracking_id)
            ->with('items')
            ->first();

        if (!$order) {
            return $this->sendError('Order not found.');
        }

        return $this->sendResponse($order, 'Order status retrieved successfully.');
    }
}
