<?php

namespace App\Http\Controllers\Api;

use App\Models\Site;
use App\Models\Product;
use Illuminate\Http\Request;

class SocialPreviewController extends BaseController
{
    public function product(Request $request, $site_slug, $slug)
    {
        $site = Site::where('slug', $site_slug)->first();
        if (!$site) {
            abort(404, 'Site not found');
        }

        $product = Product::where('site_id', $site->id)
            ->where('slug', $slug)
            ->with('images')
            ->first();

        if (!$product) {
            abort(404, 'Product not found');
        }

        // Determine storefront URL
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

        // Get primary image
        $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();

        $imageUrl = '';
        if ($primaryImage) {
            $imageUrl = $primaryImage->image_path;
            if ($imageUrl && !preg_match("~^(?:f|ht)tps?://~i", $imageUrl)) {
                $imageUrl = url($imageUrl);
            }
        } else {
            $imageUrl = $site_slug === 'acharu'
                ? 'https://images.unsplash.com/photo-1589135233689-d58620025983?q=80&w=800'
                : 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?q=80&w=800';
        }

        $title = ($product->name ?? 'Product') . ' | ' . ($site->name ?? 'Store');
        $description = $product->description ?? '';
        $description = mb_substr($description, 0, 200);

        // Default fallback description
        if (empty(trim($description))) {
            $description = "Check out {$product->name} at {$site->name}!";
        }

        return response(
            <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>

    <!-- Open Graph / Facebook / Messenger / WhatsApp / Telegram -->
    <meta property="og:type" content="product" />
    <meta property="og:url" content="{$storefrontUrl}" />
    <meta property="og:title" content="{$title}" />
    <meta property="og:description" content="{$description}" />
    <meta property="og:image" content="{$imageUrl}" />
    <meta property="og:site_name" content="{$site->name}" />

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:url" content="{$storefrontUrl}" />
    <meta name="twitter:title" content="{$title}" />
    <meta name="twitter:description" content="{$description}" />
    <meta name="twitter:image" content="{$imageUrl}" />

    <!-- Redirect to actual product page -->
    <meta http-equiv="refresh" content="0; url={$storefrontUrl}" />

    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f8fafc;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 400px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        img {
            width: 100%;
            border-radius: 12px;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 20px;
            margin: 0 0 8px;
        }
        p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="{$imageUrl}" alt="{$product->name}" />
        <h1>{$product->name}</h1>
        <p>Redirecting to product page...</p>
        <p><a href="{$storefrontUrl}">Click here if not redirected</a></p>
    </div>

    <script>
        window.location.href = '{$storefrontUrl}';
    </script>
</body>
</html>
HTML
        , 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
