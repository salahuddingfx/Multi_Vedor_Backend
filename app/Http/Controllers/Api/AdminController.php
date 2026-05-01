<?php

namespace App\Http\Controllers\Api;

use App\Models\Site;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminController extends BaseController
{
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Invalid credentials.', [], 401);
        }

        if ($user->role !== 'admin') {
            return $this->sendError('Unauthorized access.', [], 403);
        }

        $token = $user->createToken('admin_token')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ], 'Login successful.');
    }

    public function getStats(Request $request) {
        $siteId = $request->site_id;

        $stats = [
            'total_sales' => (float) Order::where('site_id', $siteId)->where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_orders' => Order::where('site_id', $siteId)->count(),
            'active_products' => Product::where('site_id', $siteId)->count(),
            'low_stock_products' => Product::where('site_id', $siteId)->where('stock', '<', 10)->count(),
            'recent_orders' => Order::where('site_id', $siteId)->latest()->take(5)->get(),
        ];

        return $this->sendResponse($stats, 'Stats retrieved.');
    }

    // Product CRUD
    public function getProducts(Request $request) {
        $products = Product::with(['category', 'site', 'images'])->paginate(20);
        return $this->sendResponse($products, 'Admin products retrieved.');
    }

    public function storeProduct(Request $request) {
        $validated = $request->validate([
            'site_id' => 'required',
            'category_id' => 'required',
            'name' => 'required',
            'price' => 'required|numeric',
            'weight' => 'required|numeric',
        ]);
        
        $validated['slug'] = Str::slug($request->name);
        $product = Product::create($validated);
        
        return $this->sendResponse($product, 'Product created.');
    }

    // Category CRUD
    public function getCategories() {
        return $this->sendResponse(Category::all(), 'All categories.');
    }

    // Order Management
    public function updateOrderStatus(Request $request, $id) {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        return $this->sendResponse($order, 'Order status updated to ' . $request->status);
    }

    // User Management (Admins)
    public function getUsers() {
        return $this->sendResponse(User::where('role', 'admin')->get(), 'Admin users retrieved.');
    }

    public function updateUser(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email', 'role']));
        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        return $this->sendResponse($user, 'User updated successfully.');
    }

    // Site Settings
    public function updateSettings(Request $request, $site_id) {
        $site = Site::findOrFail($site_id);
        $site->update(['settings' => $request->settings]);
        return $this->sendResponse($site, 'Site settings updated.');
    }
}
