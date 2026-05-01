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
use App\Models\Contact;
use App\Models\HeroSlide;
use App\Models\Page;

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

        // Calculate sales for the last 7 days for the chart efficiently
        $startDate = now()->subDays(6)->startOfDay();
        $chartDataRaw = Order::where('site_id', $siteId)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->format('D');
            $chartData[] = [
                'name' => $dayName,
                'sales' => (float) ($chartDataRaw[$date] ?? 0)
            ];
        }

        $stats = [
            'total_sales' => (float) Order::where('site_id', $siteId)->where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_orders' => Order::where('site_id', $siteId)->count(),
            'active_products' => Product::where('site_id', $siteId)->count(),
            'low_stock_products' => Product::where('site_id', $siteId)->where('stock', '<', 10)->count(),
            'recent_orders' => Order::where('site_id', $siteId)->latest()->take(5)->get(),
            'chart_data' => $chartData
        ];

        return $this->sendResponse($stats, 'Stats retrieved.');
    }

    // Product CRUD
    public function getProducts(Request $request) {
        $siteId = $request->site_id;
        $products = Product::where('site_id', $siteId)
            ->with(['category', 'site', 'images'])
            ->paginate(20);
        return $this->sendResponse($products, 'Admin products retrieved.');
    }

    public function storeProduct(Request $request) {
        $validated = $request->validate([
            'site_id' => 'required',
            'category_id' => 'required',
            'name' => 'required',
            'price' => 'required|numeric',
            'weight' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048'
        ]);
        
        $validated['slug'] = Str::slug($request->name);
        $product = Product::create($validated);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imagePath = asset('storage/' . $path);
            $product->images()->create(['image_path' => $imagePath]);
        }

        return $this->sendResponse($product->load('images'), 'Product created with image.');
    }

    public function updateProduct(Request $request, $id) {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'category_id' => 'sometimes|required',
            'name' => 'sometimes|required',
            'price' => 'sometimes|required|numeric',
            'weight' => 'sometimes|required|numeric',
            'stock' => 'sometimes|required|integer',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048'
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product->update($validated);

        if ($request->hasFile('image')) {
            // Delete old image if needed, but for now just add new one
            $path = $request->file('image')->store('products', 'public');
            $imagePath = asset('storage/' . $path);
            $product->images()->delete(); // Clear old ones for simplicity
            $product->images()->create(['image_path' => $imagePath]);
        }

        return $this->sendResponse($product->load('images'), 'Product updated.');
    }

    public function deleteProduct($id) {
        Product::findOrFail($id)->delete();
        return $this->sendResponse(null, 'Product deleted.');
    }

    // Category CRUD
    public function getCategories(Request $request) {
        $siteId = $request->site_id;
        $categories = Category::where('site_id', $siteId)->get();
        return $this->sendResponse($categories, 'Categories for site ' . $siteId);
    }

    public function storeCategory(Request $request) {
        $validated = $request->validate([
            'site_id' => 'required',
            'name' => 'required'
        ]);
        $validated['slug'] = Str::slug($request->name);
        $category = Category::create($validated);
        return $this->sendResponse($category, 'Category created.');
    }

    public function updateCategory(Request $request, $id) {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required'
        ]);
        $validated['slug'] = Str::slug($request->name);
        $category->update($validated);
        return $this->sendResponse($category, 'Category updated.');
    }

    public function deleteCategory($id) {
        Category::findOrFail($id)->delete();
        return $this->sendResponse(null, 'Category deleted.');
    }

    // Order Management
    public function getOrders(Request $request) {
        $siteId = $request->site_id;
        $orders = Order::where('site_id', $siteId)
            ->with(['items'])
            ->latest()
            ->paginate(50);
        return $this->sendResponse($orders, 'Admin orders retrieved.');
    }

    public function updateOrderStatus(Request $request, $id) {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        return $this->sendResponse($order, 'Order status updated to ' . $request->status);
    }

    public function updatePaymentStatus(Request $request, $id) {
        $order = Order::findOrFail($id);
        $order->update(['payment_status' => $request->payment_status]);
        return $this->sendResponse($order, 'Payment status updated to ' . $request->payment_status);
    }

    // User Management (Admins)
    public function getUsers() {
        return $this->sendResponse(User::where('role', 'admin')->get(), 'Admin users retrieved.');
    }

    public function generateInvoice($id) {
        $order = Order::with('items')->findOrFail($id);
        return view('invoice', compact('order'));
    }

    public function storeUser(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required'
        ]);

        $validated['password'] = Hash::make($request->password);
        $user = User::create($validated);

        return $this->sendResponse($user, 'Admin user created.');
    }

    public function updateUser(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email', 'role']));
        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        return $this->sendResponse($user, 'User updated successfully.');
    }

    public function deleteUser($id) {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return $this->sendError('You cannot delete yourself.');
        }
        $user->delete();
        return $this->sendResponse(null, 'User deleted.');
    }

    // Contact Messages
    public function getMessages(Request $request) {
        $siteId = $request->site_id;
        $messages = Contact::where('site_id', $siteId)->latest()->paginate(20);
        return $this->sendResponse($messages, 'Contact messages retrieved.');
    }

    public function markMessageRead($id) {
        $message = Contact::findOrFail($id);
        $message->update(['is_read' => true]);
        return $this->sendResponse($message, 'Message marked as read.');
    }

    // Site Settings
    public function getSettings($site_id) {
        $site = Site::findOrFail($site_id);
        return $this->sendResponse($site->settings, 'Site settings retrieved.');
    }

    public function updateSettings(Request $request, $site_id) {
        $site = Site::findOrFail($site_id);
        $site->update(['settings' => $request->settings]);
        return $this->sendResponse($site, 'Site settings updated.');
    }

    // Hero Slides
    public function getHeroSlides(Request $request) {
        $siteId = $request->site_id;
        $slides = HeroSlide::where('site_id', $siteId)->orderBy('order')->get();
        return $this->sendResponse($slides, 'Hero slides retrieved.');
    }

    public function storeHeroSlide(Request $request) {
        $validated = $request->validate([
            'site_id' => 'required',
            'title' => 'required',
            'subtitle' => 'nullable',
            'badge' => 'nullable',
            'image' => 'required|image|max:2048',
            'order' => 'integer'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('slides', 'public');
            $validated['image_path'] = asset('storage/' . $path);
        }

        $slide = HeroSlide::create($validated);
        return $this->sendResponse($slide, 'Hero slide created.');
    }

    public function deleteHeroSlide($id) {
        HeroSlide::findOrFail($id)->delete();
        return $this->sendResponse(null, 'Hero slide deleted.');
    }

    // Dynamic Pages
    public function getPages(Request $request) {
        $siteId = $request->site_id;
        $pages = Page::where('site_id', $siteId)->get();
        return $this->sendResponse($pages, 'Pages retrieved.');
    }

    public function storePage(Request $request) {
        $validated = $request->validate([
            'site_id' => 'required',
            'title' => 'required',
            'content' => 'required',
            'is_active' => 'boolean'
        ]);
        $validated['slug'] = Str::slug($request->title);
        $page = Page::create($validated);
        return $this->sendResponse($page, 'Page created.');
    }

    public function updatePage(Request $request, $id) {
        $page = Page::findOrFail($id);
        $validated = $request->validate([
            'title' => 'sometimes|required',
            'content' => 'sometimes|required',
            'is_active' => 'sometimes|boolean'
        ]);
        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        $page->update($validated);
        return $this->sendResponse($page, 'Page updated.');
    }

    public function deletePage($id) {
        Page::findOrFail($id)->delete();
        return $this->sendResponse(null, 'Page deleted.');
    }
}
