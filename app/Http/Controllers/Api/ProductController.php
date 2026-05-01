<?php

namespace App\Http\Controllers\Api;

use App\Models\Site;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function index(Request $request, $site_slug)
    {
        $site = Site::where('slug', $site_slug)->first();
        if (!$site) return $this->sendError('Site not found.');

        $query = Product::where('site_id', $site->id)->with(['images', 'category']);

        if ($request->has('category')) {
            $category = Category::where('site_id', $site->id)->where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $products = $query->paginate(12);
        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    public function show($site_slug, $slug)
    {
        $site = Site::where('slug', $site_slug)->first();
        if (!$site) return $this->sendError('Site not found.');

        $product = Product::where('site_id', $site->id)
            ->where('slug', $slug)
            ->with(['images', 'category'])
            ->first();

        if (!$product) {
            return $this->sendError('Product not found.');
        }

        return $this->sendResponse($product, 'Product retrieved successfully.');
    }
}
