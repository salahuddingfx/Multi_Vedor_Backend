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
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Contact;
use App\Models\HeroSlide;
use App\Models\Page;
use App\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Cache;

class AdminController extends BaseController
{
    public function me(Request $request) {
        return $this->sendResponse($request->user(), 'Profile retrieved.');
    }

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
                'role' => $user->role,
                'image_path' => $user->image_path
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
                'value' => (float) ($chartDataRaw[$date] ?? 0)
            ];
        }

        // Sales trend (this week vs last week)
        $thisWeekSales = Order::where('site_id', $siteId)->where('status', '!=', 'cancelled')->where('created_at', '>=', now()->subDays(7))->sum('total_amount');
        $lastWeekSales = Order::where('site_id', $siteId)->where('status', '!=', 'cancelled')->where('created_at', '>=', now()->subDays(14))->where('created_at', '<', now()->subDays(7))->sum('total_amount');
        $growth = $lastWeekSales > 0 ? (($thisWeekSales - $lastWeekSales) / $lastWeekSales) * 100 : 100;

        $stats = [
            'totalSales' => (float) Order::where('site_id', $siteId)->where('status', '!=', 'cancelled')->sum('total_amount'),
            'totalOrders' => Order::where('site_id', $siteId)->count(),
            'activeProducts' => Product::where('site_id', $siteId)->count(),
            'lowStock' => Product::where('site_id', $siteId)->where('stock', '<', 10)->count(),
            'lowStockProducts' => Product::where('site_id', $siteId)->where('stock', '<', 10)->with('category')->get(),
            'recentSales' => Order::where('site_id', $siteId)->latest()->take(5)->get()->map(function($o) {
                return [
                    'id' => $o->id,
                    'date' => $o->created_at->format('d M, Y'),
                    'amount' => (float)$o->total_amount,
                    'status' => $o->status
                ];
            }),
            'chartData' => $chartData,
            'growth' => round($growth, 1)
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
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'weight' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'name_bn' => 'nullable|string|max:255',
            'description_bn' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $validated = $validator->validated();
        
        $validated['slug'] = Str::slug($request->name);
        $product = Product::create($validated);
        
        if ($request->hasFile('images')) {
            $primaryIndex = (int) $request->input('primary_image_index', 0);
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $imagePath = asset('storage/' . $path);
                $product->images()->create([
                    'image_path' => $imagePath,
                    'is_primary' => $index === $primaryIndex
                ]);
            }
        }

        return $this->sendResponse($product->load('images'), 'Product created with images.');
    }

    public function updateProduct(Request $request, $id) {
        $product = Product::findOrFail($id);
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'nullable|string|max:50',
            'price' => 'sometimes|required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'weight' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'description' => 'nullable|string',
            'name_bn' => 'nullable|string|max:255',
            'description_bn' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $validated = $validator->validated();

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product->update($validated);

        if ($request->hasFile('images')) {
            $product->images()->delete(); // Clear old ones for simplicity
            $primaryIndex = (int) $request->input('primary_image_index', 0);
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $imagePath = asset('storage/' . $path);
                $product->images()->create([
                    'image_path' => $imagePath,
                    'is_primary' => $index === $primaryIndex
                ]);
            }
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
            'name' => 'required',
            'is_featured' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);
        
        $validated['slug'] = Str::slug($request->name);
        
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('uploads/categories'), $imageName);
            $validated['image_path'] = url('uploads/categories/' . $imageName);
        }

        $category = Category::create($validated);
        return $this->sendResponse($category, 'Category created.');
    }

    public function updateCategory(Request $request, $id) {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required',
            'is_featured' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);
        
        $validated['slug'] = Str::slug($request->name);

        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('uploads/categories'), $imageName);
            $validated['image_path'] = url('uploads/categories/' . $imageName);
        }

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
        
        // Dispatch event for real-time tracking update
        event(new OrderStatusChanged($order));
        
        return $this->sendResponse($order, 'Order status updated to ' . $request->status);
    }

    public function updatePaymentStatus(Request $request, $id) {
        $order = Order::findOrFail($id);
        $order->update(['payment_status' => $request->payment_status]);
        
        // Dispatch event for real-time tracking update
        event(new OrderStatusChanged($order));
        
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

    // Hero Slides
    public function getHeroSlides(Request $request) {
        $siteId = $request->site_id;
        $slides = HeroSlide::where('site_id', $siteId)->orderBy('order')->get();
        return $this->sendResponse($slides, 'Hero slides retrieved.');
    }

    public function storeHeroSlide(Request $request) {
        $validated = $request->validate([
            'site_id' => 'required',
            'product_id' => 'nullable|integer',
            'title' => 'required',
            'subtitle' => 'nullable',
            'badge' => 'nullable',
            'button_text' => 'nullable',
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

    public function updateHeroSlide(Request $request, $id) {
        $slide = HeroSlide::findOrFail($id);
        $validated = $request->validate([
            'product_id' => 'nullable|integer',
            'title' => 'sometimes|required',
            'subtitle' => 'nullable',
            'badge' => 'nullable',
            'button_text' => 'nullable',
            'image' => 'nullable|image|max:2048',
            'order' => 'integer'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('slides', 'public');
            $validated['image_path'] = asset('storage/' . $path);
        }

        $slide->update($validated);
        return $this->sendResponse($slide, 'Hero slide updated.');
    }

    public function deleteHeroSlide($id) {
        HeroSlide::findOrFail($id)->delete();
        return $this->sendResponse(null, 'Hero slide deleted.');
    }


    public function storeUser(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('avatars', 'public');
            $validated['image_path'] = asset('storage/' . $path);
        }

        $validated['password'] = Hash::make($request->password);
        $user = User::create($validated);

        return $this->sendResponse($user, 'Admin user created.');
    }

    public function updateUser(Request $request, $id) {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|required',
            'image' => 'nullable|image|max:2048'
        ]);

        $user->update($request->only(['name', 'email', 'role']));

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('avatars', 'public');
            $user->update(['image_path' => asset('storage/' . $path)]);
        }

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

    public function getSalesStats(Request $request)
    {
        $siteId = $request->query('site_id');
        $range = $request->query('range', 'monthly');
        
        $cacheKey = "sales_stats_{$siteId}_{$range}";
        
        return Cache::remember($cacheKey, 300, function() use ($siteId, $range) {
            $now = now();
            $startDate = match($range) {
                'daily' => $now->subHours(24),
                'weekly' => $now->subDays(7),
                'monthly' => $now->subDays(30),
                '90days' => $now->subDays(90),
                'yearly' => $now->subYear(),
                default => $now->subDays(30),
            };

            // 1. Aggregated Base Stats (Single SQL Query for Main Metrics)
            $statsQuery = DB::table('orders')
                ->where('created_at', '>=', $startDate)
                ->where('status', '!=', 'cancelled');
            
            if ($siteId) {
                $statsQuery->where('site_id', $siteId);
            }

            $baseStats = $statsQuery->select([
                DB::raw('SUM(subtotal) as total_product_price'),
                DB::raw('SUM(delivery_charge) as total_delivery_charge'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COUNT(DISTINCT customer_phone) as total_customers')
            ])->first();

            // 2. Returns Stats
            $returnsQuery = DB::table('product_returns')
                ->join('products', 'product_returns.product_id', '=', 'products.id')
                ->where('product_returns.created_at', '>=', $startDate);
                
            if ($siteId) {
                $returnsQuery->where('products.site_id', $siteId);
            }
            $totalReturns = (float)$returnsQuery->sum('product_returns.amount');

            // 3. Logistics Loss (Delivery charges of Returned or Cancelled orders)
            $logisticsLossQuery = DB::table('orders')
                ->where('created_at', '>=', $startDate)
                ->whereIn('status', ['returned', 'cancelled']);
            if ($siteId) {
                $logisticsLossQuery->where('site_id', $siteId);
            }
            $logisticsLoss = (float)$logisticsLossQuery->sum('delivery_charge');

            // 4. Cancelled Stats
            $cancelledQuery = DB::table('orders')
                ->where('created_at', '>=', $startDate)
                ->where('status', 'cancelled');
            if ($siteId) {
                $cancelledQuery->where('site_id', $siteId);
            }
            $cancelledStats = $cancelledQuery->select([
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as value')
            ])->first();

            // 5. Top Selling Products (Optimized)
            $topProductsQuery = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.created_at', '>=', $startDate)
                ->whereNotIn('orders.status', ['cancelled', 'returned']);
                
            if ($siteId) {
                $topProductsQuery->where('orders.site_id', $siteId);
            }

            $topProducts = $topProductsQuery->select(
                    'products.name', 
                    DB::raw('SUM(order_items.quantity) as units'), 
                    DB::raw('SUM(order_items.price * order_items.quantity) as revenue')
                )
                ->groupBy('products.id', 'products.name')
                ->orderBy('revenue', 'desc')
                ->limit(5)
                ->get();

            // 6. Order Status Distribution
            $statusDistributionQuery = DB::table('orders')
                ->where('created_at', '>=', $startDate);
            if ($siteId) {
                $statusDistributionQuery->where('site_id', $siteId);
            }
            $statusDistribution = $statusDistributionQuery->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            // 7. Low Stock Products
            $lowStockProductsQuery = DB::table('products')
                ->where('stock', '<', 10);
            if ($siteId) {
                $lowStockProductsQuery->where('site_id', $siteId);
            }
            $lowStockProducts = $lowStockProductsQuery->limit(5)->get();

            // 8. Site Breakdown
            $siteBreakdown = [];
            if (!$siteId) {
                $siteBreakdown = DB::table('orders')
                    ->join('sites', 'orders.site_id', '=', 'sites.id')
                    ->where('orders.created_at', '>=', $startDate)
                    ->whereNotIn('orders.status', ['cancelled', 'returned'])
                    ->select('sites.name', DB::raw('SUM(total_amount) as revenue'))
                    ->groupBy('sites.id', 'sites.name')
                    ->get();
            }

            // 9. Chart Data
            $chartData = $this->getOptimizedChartData($startDate, $siteId, $range);

            $totalProductPrice = (float)($baseStats->total_product_price ?? 0);
            $totalDelivery = (float)($baseStats->total_delivery_charge ?? 0);
            $netRevenue = $totalProductPrice - $totalReturns - $logisticsLoss;

            return [
                'total_product_price' => $totalProductPrice,
                'total_delivery_charge' => $totalDelivery,
                'logistics_loss' => $logisticsLoss,
                'total_revenue' => $netRevenue,
                'total_orders' => (int)($baseStats->total_orders ?? 0),
                'total_customers' => (int)($baseStats->total_customers ?? 0),
                'total_returns' => $totalReturns,
                'total_cancelled_orders' => (int)($cancelledStats->count ?? 0),
                'total_cancelled_value' => (float)($cancelledStats->value ?? 0),
                'avg_order_value' => $baseStats->total_orders > 0 ? (float)($netRevenue / $baseStats->total_orders) : 0,
                'avg_delivery_fee' => $baseStats->total_orders > 0 ? (float)($totalDelivery / $baseStats->total_orders) : 0,
                'chart_data' => $chartData,
                'top_products' => $topProducts,
                'status_distribution' => $statusDistribution,
                'low_stock_products' => $lowStockProducts,
                'site_breakdown' => $siteBreakdown
            ];
        });
    }

    private function getOptimizedChartData($startDate, $siteId, $range)
    {
        $dateFormat = match($range) {
            'daily' => '%H:00',
            'yearly' => '%M %Y',
            default => '%d %b',
        };

        $query = DB::table('orders')
            ->where('created_at', '>=', $startDate)
            ->where('status', '!=', 'cancelled');

        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        return $query->select([
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as name"),
                DB::raw('SUM(total_amount) as sales'),
                DB::raw('COUNT(*) as orders')
            ])
            ->groupBy('name')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function recordReturn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'order_id' => 'nullable|exists:orders,id',
            'reason' => 'nullable|string'
        ]);

        $product = Product::findOrFail($request->product_id);
        
        // Record in database
        DB::table('product_returns')->insert([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'amount' => $product->price * $request->quantity,
            'order_id' => $request->order_id,
            'reason' => $request->reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Increase stock
        $product->increment('stock_quantity', $request->quantity);
        
        return response()->json([
            'message' => 'Return recorded and stock updated.',
            'new_stock' => $product->stock_quantity
        ]);
    }

    // Site Settings with Cache
    public function getSettings($site_id) {
        return Cache::remember("site_settings_{$site_id}", 3600, function() use ($site_id) {
            $site = Site::findOrFail($site_id);
            return $this->sendResponse($site->settings, 'Site settings retrieved.');
        });
    }

    public function updateSettings(Request $request, $site_id) {
        $site = Site::findOrFail($site_id);
        $site->update(['settings' => $request->settings]);
        Cache::forget("site_settings_{$site_id}");
        return $this->sendResponse($site->settings, 'Site settings updated.');
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

    public function getReturns()
    {
        $returns = DB::table('product_returns')->latest()->get();
        return $this->sendResponse($returns, 'Returns retrieved successfully.');
    }
}
