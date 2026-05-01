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
        $site = Site::where('slug', $site_slug)->with(['categories', 'heroSlides'])->firstOrFail();
        
        return response()->json([
            'site' => $site,
            'categories' => $site->categories,
            'hero_slides' => $site->heroSlides,
        ]);
    }

    /**
     * Get products with optional category filtering.
     */
    public function getProducts(Request $request, $site_slug)
    {
        $site = Site::where('slug', $site_slug)->firstOrFail();
        
        $query = Product::where('site_id', $site->id);
        
        if ($request->has('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        return response()->json($query->paginate(12));
    }

    /**
     * Get detailed product information.
     */
    public function getProductDetails($site_slug, $slug)
    {
        $site = Site::where('slug', $site_slug)->firstOrFail();
        $product = Product::where('site_id', $site->id)
                          ->where('slug', $slug)
                          ->with('category')
                          ->firstOrFail();
                          
        return response()->json($product);
    }
}
