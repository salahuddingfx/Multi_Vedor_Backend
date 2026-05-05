<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Initialize store data: settings, categories, and hero slides.
     */
    public function initialize($site_slug)
    {
        return \Illuminate\Support\Facades\Cache::remember("init_{$site_slug}", 3600, function() use ($site_slug) {
            $site = Site::where('slug', $site_slug)->with(['categories', 'heroSlides'])->firstOrFail();
            return [
                'site' => $site,
                'categories' => $site->categories,
                'hero_slides' => $site->heroSlides,
            ];
        });
    }

    /**
     * Get products with optional category filtering.
     */
    public function getProducts(Request $request, $site_slug)
    {
        $categorySlug = $request->query('category');
        $isFeatured = $request->has('featured');
        $page = $request->query('page', 1);
        
        $cacheKey = "products_{$site_slug}_{$categorySlug}_{$isFeatured}_{$page}";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function() use ($request, $site_slug, $categorySlug, $isFeatured) {
            $site = Site::where('slug', $site_slug)->firstOrFail();
            $query = Product::where('site_id', $site->id)->with('images');
            
            if ($categorySlug) {
                $category = Category::where('slug', $categorySlug)->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                }
            }

            if ($isFeatured) {
                $query->where('is_featured', true);
            }

            return $query->paginate(12);
        });
    }

    /**
     * Get detailed product information.
     */
    public function getProductDetails($site_slug, $slug)
    {
        return \Illuminate\Support\Facades\Cache::remember("product_{$site_slug}_{$slug}", 3600, function() use ($site_slug, $slug) {
            $site = Site::where('slug', $site_slug)->firstOrFail();
            return Product::where('site_id', $site->id)
                          ->where('slug', $slug)
                          ->with(['category', 'images'])
                          ->firstOrFail();
        });
    }
}
