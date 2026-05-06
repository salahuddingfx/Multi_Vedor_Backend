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
use App\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Cache;
use App\Models\ProductVariation;

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
        $cacheKey = "admin_dashboard_stats_{$siteId}";

        $stats = Cache::remember($cacheKey, 60, function() use ($siteId) {
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

            return [
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
        });

        return $this->sendResponse($stats, 'Stats retrieved.');
    }

    // Product CRUD
    public function getProducts(Request $request) {
        $siteId = $request->site_id;
        $products = Product::where('site_id', $siteId)
            ->with(['category', 'site', 'images', 'variations'])
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

        // Handle Variations
        if ($request->has('variations')) {
            $variations = is_array($request->variations) ? $request->variations : json_decode($request->variations, true);
            foreach ($variations as $v) {
                $product->variations()->create([
                    'weight' => $v['weight'],
                    'price' => $v['price'],
                    'original_price' => $v['original_price'] ?? null,
                    'stock' => $v['stock'] ?? 0,
                    'sku' => $v['sku'] ?? null
                ]);
            }
        }

        // Clear product caches
        $this->clearStorefrontCache($product->site_id);

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

        // Handle deleted images
        if ($request->has('deleted_image_ids')) {
            $deletedIds = is_array($request->deleted_image_ids) ? $request->deleted_image_ids : explode(',', $request->deleted_image_ids);
            foreach ($product->images()->whereIn('id', $deletedIds)->get() as $oldImg) {
                $this->deleteFileFromPath($oldImg->image_path);
                $oldImg->delete();
            }
        }

        // Handle primary image update
        if ($request->has('primary_image_id')) {
            $product->images()->update(['is_primary' => false]);
            $product->images()->where('id', $request->primary_image_id)->update(['is_primary' => true]);
        } elseif ($request->has('primary_image_index')) {
            $product->images()->update(['is_primary' => false]);
        }

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

        // Handle Variations Update (smart sync)
        if ($request->has('variations')) {
            $variations = is_array($request->variations) ? $request->variations : json_decode($request->variations, true);
            
            if (is_array($variations)) {
                // IDs of variations the client still wants to keep
                $keptIds = array_values(array_filter(array_map(fn($v) => $v['id'] ?? null, $variations)));

                // Delete variations removed by the client
                if (!empty($keptIds)) {
                    $product->variations()->whereNotIn('id', $keptIds)->delete();
                } else {
                    // No IDs present — all rows are new; don't delete existing ones if user sent non-empty list
                    // Only delete all if list is completely empty (user removed all)
                    if (empty($variations)) {
                        $product->variations()->delete();
                    }
                }

                foreach ($variations as $v) {
                    if (empty($v['weight']) || !isset($v['price'])) continue;

                    $payload = [
                        'product_id'     => $product->id,
                        'weight'         => $v['weight'],
                        'price'          => $v['price'],
                        'original_price' => !empty($v['original_price']) ? $v['original_price'] : null,
                        'stock'          => $v['stock'] ?? 0,
                        'sku'            => !empty($v['sku']) ? $v['sku'] : null,
                    ];

                    if (!empty($v['id'])) {
                        // Existing variation — update it
                        $product->variations()->where('id', $v['id'])->update($payload);
                    } else {
                        // New variation — create it
                        $product->variations()->create($payload);
                    }
                }
            }
        }

        $this->clearStorefrontCache($product->site_id);

        return $this->sendResponse($product->load('images'), 'Product updated.');
    }

    public function deleteProduct($id) {
        $product = Product::findOrFail($id);
        foreach($product->images as $oldImg) {
            $this->deleteFileFromPath($oldImg->image_path);
        }
        $siteId = $product->site_id;
        $product->delete();
        $this->clearStorefrontCache($siteId);
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
            'is_featured' => 'boolean'
        ]);
        
        $validated['slug'] = Str::slug($request->name);
        $category = Category::create($validated);
        
        $this->clearStorefrontCache($request->site_id);
        
        return $this->sendResponse($category, 'Category created.');
    }

    public function updateCategory(Request $request, $id) {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required',
            'is_featured' => 'boolean'
        ]);
        
        $validated['slug'] = Str::slug($request->name);
        $category->update($validated);
        
        $this->clearStorefrontCache($category->site_id);
        
        return $this->sendResponse($category, 'Category updated.');
    }

    public function deleteCategory($id) {
        $category = Category::findOrFail($id);
        $siteId = $category->site_id;
        $category->delete();
        $this->clearStorefrontCache($siteId);
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
        $oldStatus = $order->status;
        $newStatus = $request->status;
        $order->update(['status' => $newStatus]);
        
        // Stock adjustment on cancellation status change
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                $prod = Product::find($item->product_id);
                if ($prod) {
                    if ($item->variation_id && $item->variation_id !== 'base') {
                        $variation = $prod->variations()->find($item->variation_id);
                        if ($variation) {
                            $variation->increment('stock', $item->quantity);
                        }
                    } else {
                        $prod->increment('stock', $item->quantity);
                    }
                }
            }
        } else if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                $prod = Product::find($item->product_id);
                if ($prod) {
                    if ($item->variation_id && $item->variation_id !== 'base') {
                        $variation = $prod->variations()->find($item->variation_id);
                        if ($variation) {
                            $variation->decrement('stock', $item->quantity);
                        }
                    } else {
                        $prod->decrement('stock', $item->quantity);
                    }
                }
            }
        }
        
        event(new OrderStatusChanged($order));
        $this->clearStorefrontCache($order->site_id);
        
        return $this->sendResponse($order, 'Order status updated to ' . $newStatus);
    }

    public function updatePaymentStatus(Request $request, $id) {
        $order = Order::findOrFail($id);
        $order->update(['payment_status' => $request->payment_status]);
        
        event(new OrderStatusChanged($order));
        
        return $this->sendResponse($order, 'Payment status updated to ' . $request->payment_status);
    }

    public function updateOrder(Request $request, $id) {
        $order = Order::findOrFail($id);
        $order->update($request->only([
            'customer_name', 
            'customer_phone', 
            'customer_address', 
            'location'
        ]));
        
        return $this->sendResponse($order, 'Order details updated.');
    }

    public function deleteOrder($id) {
        $order = Order::findOrFail($id);
        $order->delete();
        return $this->sendResponse(null, 'Order deleted successfully.');
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
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'site_id' => 'required',
            'product_id' => 'nullable|integer',
            'title' => 'required',
            'subtitle' => 'nullable',
            'badge' => 'nullable',
            'button_text' => 'nullable',
            'image' => 'required|image|max:5120',
            'order' => 'integer'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('slides', 'public');
            $validated['image_path'] = asset('storage/' . $path);
        }

        $slide = HeroSlide::create($validated);
        $this->clearStorefrontCache($request->site_id);
        return $this->sendResponse($slide, 'Hero slide created.');
    }

    public function updateHeroSlide(Request $request, $id) {
        $slide = HeroSlide::findOrFail($id);
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'product_id' => 'nullable|integer',
            'title' => 'sometimes|required',
            'subtitle' => 'nullable',
            'badge' => 'nullable',
            'button_text' => 'nullable',
            'image' => 'nullable|image|max:5120',
            'order' => 'integer'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('image')) {
            $this->deleteFileFromPath($slide->image_path);
            $path = $request->file('image')->store('slides', 'public');
            $validated['image_path'] = asset('storage/' . $path);
        }

        $slide->update($validated);
        $this->clearStorefrontCache($slide->site_id);
        return $this->sendResponse($slide, 'Hero slide updated.');
    }

    public function deleteHeroSlide($id) {
        $slide = HeroSlide::findOrFail($id);
        $this->deleteFileFromPath($slide->image_path);
        $siteId = $slide->site_id;
        $slide->delete();
        $this->clearStorefrontCache($siteId);
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
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');
        
        $cacheKey = "sales_stats_{$siteId}_{$range}_{$customStart}_{$customEnd}";
        
        return Cache::remember($cacheKey, 300, function() use ($siteId, $range, $customStart, $customEnd) {
            $now = now();
            
            if ($customStart && $customEnd) {
                $startDate = \Illuminate\Support\Carbon::parse($customStart)->startOfDay();
                $endDate = \Illuminate\Support\Carbon::parse($customEnd)->endOfDay();
            } else {
                $startDate = match($range) {
                    'daily' => $now->copy()->subHours(24),
                    'weekly' => $now->copy()->subDays(7),
                    'monthly' => $now->copy()->subDays(30),
                    '90days' => $now->copy()->subDays(90),
                    'yearly' => $now->copy()->subYear(),
                    default => $now->copy()->subDays(30),
                };
                $endDate = $now;
            }

            // Update all queries below to use both $startDate and $endDate

            // 1. Aggregated Base Stats (Single SQL Query for Main Metrics)
            $statsQuery = DB::table('orders')
                ->whereBetween('created_at', [$startDate, $endDate]);
            
            if ($siteId) {
                $statsQuery->where('site_id', $siteId);
            }

            $baseStats = $statsQuery->select([
                // Realized Revenue: Only Delivered orders
                DB::raw("SUM(CASE WHEN status = 'delivered' THEN subtotal ELSE 0 END) as realized_revenue"),
                // Total Value: All non-cancelled orders
                DB::raw("SUM(CASE WHEN status != 'cancelled' THEN subtotal ELSE 0 END) as total_product_price"),
                DB::raw('SUM(delivery_charge) as total_delivery_charge'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COUNT(DISTINCT customer_phone) as total_customers')
            ])->first();

            // 2. Returns Stats
            $returnsQuery = DB::table('product_returns')
                ->join('products', 'product_returns.product_id', '=', 'products.id')
                ->whereBetween('product_returns.created_at', [$startDate, $endDate]);
                
            if ($siteId) {
                $returnsQuery->where('products.site_id', $siteId);
            }
            $totalReturns = (float)$returnsQuery->sum('product_returns.amount');

            // 3. Logistics Loss (Delivery charges of Returned or Cancelled orders)
            $logisticsLossQuery = DB::table('orders')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['returned', 'cancelled']);
            if ($siteId) {
                $logisticsLossQuery->where('site_id', $siteId);
            }
            $logisticsLoss = (float)$logisticsLossQuery->sum('delivery_charge');

            // 4. Cancelled Stats
            $cancelledQuery = DB::table('orders')
                ->whereBetween('created_at', [$startDate, $endDate])
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
                ->whereBetween('orders.created_at', [$startDate, $endDate])
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
                ->whereBetween('created_at', [$startDate, $endDate]);
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
                    ->whereBetween('orders.created_at', [$startDate, $endDate])
                    ->whereNotIn('orders.status', ['cancelled', 'returned'])
                    ->select('sites.name', DB::raw('SUM(total_amount) as revenue'))
                    ->groupBy('sites.id', 'sites.name')
                    ->get();
            }

            // 9. Detailed Activity Timeline (Master Report)
            $ordersTimeline = DB::table('orders')
                ->whereBetween('created_at', [$startDate, $endDate]);
            if ($siteId) {
                $ordersTimeline->where('site_id', $siteId);
            }
            $ordersTimeline = $ordersTimeline->select([
                DB::raw("'order' as type"),
                'id',
                'customer_name as title',
                'total_amount as value',
                'status as detail',
                'created_at'
            ])->get();

            $returnsTimeline = DB::table('product_returns')
                ->join('products', 'product_returns.product_id', '=', 'products.id')
                ->whereBetween('product_returns.created_at', [$startDate, $endDate]);
            if ($siteId) {
                $returnsTimeline->where('products.site_id', $siteId);
            }
            $returnsTimeline = $returnsTimeline->select([
                DB::raw("'return' as type"),
                'product_returns.id',
                'products.name as title',
                'product_returns.amount as value',
                'product_returns.reason as detail',
                'product_returns.created_at'
            ])->get();

            $timeline = $ordersTimeline->concat($returnsTimeline)->sortByDesc('created_at')->values();

            // 10. Chart Data
            $chartData = $this->getOptimizedChartData($startDate, $endDate, $siteId, $range);

            $totalProductPrice = (float)($baseStats->total_product_price ?? 0);
            $realizedRevenue = (float)($baseStats->realized_revenue ?? 0);
            $totalDelivery = (float)($baseStats->total_delivery_charge ?? 0);
            
            // Net Profit estimation now uses Realized Revenue as base
            $netRevenue = $realizedRevenue - $totalReturns - $logisticsLoss;

            return [
                'total_product_price' => $totalProductPrice,
                'realized_revenue' => $realizedRevenue,
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
                'site_breakdown' => $siteBreakdown,
                'timeline' => $timeline
            ];
        });
    }

    private function getOptimizedChartData($startDate, $endDate, $siteId, $range)
    {
        $dateFormat = match($range) {
            'daily' => '%H:00',
            'yearly' => '%M %Y',
            default => '%d %b',
        };

        $query = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
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


    public function getReturns()
    {
        $returns = DB::table('product_returns')->latest()->get();
        return $this->sendResponse($returns, 'Returns retrieved successfully.');
    }

    /**
     * Clear all storefront-related caches for a specific site.
     */
    private function clearStorefrontCache($siteId) {
        $site = Site::find($siteId);
        if (!$site) return;

        Cache::forget("init_{$site->slug}");
        Cache::forget("site_settings_{$siteId}");
        
        // Note: For product pagination caches, we might need a more sophisticated approach 
        // like cache tags if using a driver that supports them (like redis).
        // Since we are likely using 'file' or 'database', we'll just clear the main ones.
    }

    /**
     * Delete a file from the storage/public disk given its URL/path.
     */
    private function deleteFileFromPath($url) {
        if (!$url) return;
        
        // Extract relative path from URL (e.g., http://.../storage/slides/xyz.jpg -> slides/xyz.jpg)
        $path = str_replace(asset('storage/'), '', $url);
        
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
    }
}
