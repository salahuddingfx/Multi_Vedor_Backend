<?php

namespace App\Http\Controllers\Api;

use App\Models\Site;
use App\Models\HeroSlide;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteController extends BaseController
{
    public function init($site_slug)
    {
        $data = Cache::remember("init_{$site_slug}", 3600, function () use ($site_slug) {
            $site = Site::where('slug', $site_slug)->first();

            if (!$site) {
                return null;
            }

            return [
                'site' => $site,
                'hero_slides' => HeroSlide::where('site_id', $site->id)->orderBy('order')->get(),
                'categories' => Category::where('site_id', $site->id)->get(),
                'featured_products' => Product::where('site_id', $site->id)->where('is_featured', true)->with(['images', 'category'])->get(),
            ];
        });

        if (!$data) {
            return $this->sendError('Site not found.');
        }

        return $this->sendResponse($data, 'Site initialization data retrieved successfully.');
    }

    public function version($site_slug)
    {
        $site = Site::where('slug', $site_slug)->first();
        if (!$site) {
            return $this->sendError('Site not found.');
        }

        $version = Cache::get("storefront_version_{$site->id}", 0);

        return response()->json(['version' => $version]);
    }
}
