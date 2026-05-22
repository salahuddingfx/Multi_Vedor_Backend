<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SEOController extends BaseController
{
    public function generateSitemap($site)
    {
        $site_slug = $site;
        $site = Site::where('slug', $site_slug)->first();
        if (!$site) {
            return response()->json(['message' => 'Site not found'], 404);
        }

        $baseUrl = $site_slug === 'acharu' ? 'https://acharu.com' : 'https://tajashutki.com';
        
        $urls = [];

        // Static Pages
        $staticPages = ['', '/shop', '/about', '/contact', '/faq', '/reviews', '/track'];
        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => $baseUrl . $page,
                'lastmod' => now()->toAtomString(),
                'priority' => $page === '' ? '1.0' : '0.8',
                'changefreq' => 'daily'
            ];
        }

        // Categories
        $categories = Category::where('site_id', $site->id)->get();
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $baseUrl . '/shop?category=' . urlencode($category->name),
                'lastmod' => $category->updated_at ? $category->updated_at->toAtomString() : now()->toAtomString(),
                'priority' => '0.7',
                'changefreq' => 'weekly'
            ];
        }

        // Products
        $products = Product::where('site_id', $site->id)->get();
        foreach ($products as $product) {
            $urls[] = [
                'loc' => $baseUrl . '/product/' . $product->slug,
                'lastmod' => $product->updated_at ? $product->updated_at->toAtomString() : now()->toAtomString(),
                'priority' => '0.9',
                'changefreq' => 'daily'
            ];
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        
        foreach ($urls as $url) {
            $urlTag = $xml->addChild('url');
            $urlTag->addChild('loc', htmlspecialchars($url['loc']));
            $urlTag->addChild('lastmod', $url['lastmod']);
            $urlTag->addChild('changefreq', $url['changefreq']);
            $urlTag->addChild('priority', $url['priority']);
        }

        return Response::make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function showProductSEO($site_slug, $slug)
    {
        $site = Site::where('slug', $site_slug)->first();
        if (!$site) {
            abort(404, 'Site not found');
        }

        $product = Product::where('site_id', $site->id)
            ->where('slug', $slug)
            ->with(['images', 'category'])
            ->first();

        if (!$product) {
            abort(404, 'Product not found');
        }

        // Determine storefront base URL
        if (app()->environment('local')) {
            $storefrontBase = $site_slug === 'acharu' 
                ? env('ACHARU_URL', 'http://localhost:5173') 
                : env('TAJASHUTKI_URL', 'http://localhost:5174');
        } else {
            $storefrontBase = $site_slug === 'acharu' 
                ? env('ACHARU_URL', 'https://acharu.com') 
                : env('TAJASHUTKI_URL', 'https://tajashutki.com');
        }

        $storefrontUrl = rtrim($storefrontBase, '/') . '/product/' . $product->slug;

        // Image URL mapping
        $primaryImage = $product->images->where('is_primary', true)->first();
        if (!$primaryImage) {
            $primaryImage = $product->images->first();
        }

        $imageUrl = '';
        if ($primaryImage) {
            $imageUrl = $primaryImage->image_path;
        } else {
            // Fallback default image or storefront logo/favicon
            $imageUrl = $site_slug === 'acharu'
                ? 'https://images.unsplash.com/photo-1589135233689-d58620025983?q=80&w=800'
                : 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?q=80&w=800';
        }

        // Handle relative image paths
        if ($imageUrl && !preg_match("~^(?:f|ht)tps?://~i", $imageUrl)) {
            $imageUrl = url($imageUrl);
        }

        // Metadata extraction
        $title = ($product->name ?? 'Product') . ' | ' . ($site->name ?? 'Store');
        // Fallback or sub description limits
        $description = $product->description ?? 'Check out this amazing product on our store.';
        // Strip tags and trim
        $description = strip_tags($description);
        if (strlen($description) > 160) {
            $description = mb_substr($description, 0, 157) . '...';
        }

        $price = $product->price ?? '0';
        $siteName = $site->name ?? 'Store';

        // Canonical URL is the crawler URL itself
        $canonicalUrl = url()->current();

        return view('seo_preview', [
            'title' => $title,
            'description' => $description,
            'image_url' => $imageUrl,
            'canonical_url' => $canonicalUrl,
            'storefront_url' => $storefrontUrl,
            'price' => $price,
            'site_name' => $siteName
        ]);
    }
}

