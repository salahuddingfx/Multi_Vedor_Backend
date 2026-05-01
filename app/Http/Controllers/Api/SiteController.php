<?php

namespace App\Http\Controllers\Api;

use App\Models\Site;
use App\Models\HeroSlide;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SiteController extends BaseController
{
    public function init($site_slug)
    {
        $site = Site::where('slug', $site_slug)->first();

        if (!$site) {
            return $this->sendError('Site not found.');
        }

        $data = [
            'site' => $site,
            'hero_slides' => HeroSlide::where('site_id', $site->id)->orderBy('order')->get(),
            'categories' => Category::where('site_id', $site->id)->get(),
            'featured_products' => Product::where('site_id', $site->id)->where('is_featured', true)->with(['images', 'category'])->get(),
        ];

        return $this->sendResponse($data, 'Site initialization data retrieved successfully.');
    }
}
