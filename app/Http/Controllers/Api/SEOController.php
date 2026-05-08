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

        $baseUrl = $site_slug === 'acharu' ? 'https://acharu.com.bd' : 'https://tajashutki.com.bd';
        
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
        $categories = Category::all();
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $baseUrl . '/shop?category=' . urlencode($category->name),
                'lastmod' => $category->updated_at ? $category->updated_at->toAtomString() : now()->toAtomString(),
                'priority' => '0.7',
                'changefreq' => 'weekly'
            ];
        }

        // Products
        $products = Product::where('site_id', $site->id)->where('status', 'active')->get();
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
}
