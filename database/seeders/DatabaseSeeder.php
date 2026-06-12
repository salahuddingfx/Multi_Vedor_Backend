<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Site;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Multiple Admin Users
        User::create([
            'name' => 'Main Admin',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'image_path' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=200'
        ]);

        User::create([
            'name' => 'Support Admin',
            'username' => 'support',
            'email' => 'support@gmail.com',
            'password' => bcrypt('support123'),
            'role' => 'admin',
            'image_path' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?q=80&w=200'
        ]);

        // 2. Create Sites
        $acharu = Site::create([
            'name' => 'Acharu',
            'slug' => 'acharu',
            'settings' => ['primary_color' => '#800000', 'contact' => '01700000000']
        ]);

        $tajashutki = Site::create([
            'name' => 'TajaShutki',
            'slug' => 'tajashutki',
            'settings' => ['primary_color' => '#1A365D', 'contact' => '01800000000']
        ]);

        // 2. Create Categories
        $p1 = Category::create(['site_id' => $acharu->id, 'name' => 'Pickles', 'slug' => 'pickles']);
        $s1 = Category::create(['site_id' => $tajashutki->id, 'name' => 'Dry Fish', 'slug' => 'dry-fish']);

        // 3. Create Products for Acharu
        $naga = Product::create([
            'site_id' => $acharu->id,
            'category_id' => $p1->id,
            'name' => 'Premium Naga Pickle',
            'slug' => 'naga-pickle',
            'price' => 250,
            'weight' => 0.4,
            'stock' => 100,
            'is_featured' => true
        ]);
        ProductImage::create(['product_id' => $naga->id, 'image_path' => 'https://images.unsplash.com/photo-1589135233689-d58620025983?q=80&w=800', 'is_primary' => true]);

        // 4. Create Products for Taja Shutki
        $loitta = Product::create([
            'site_id' => $tajashutki->id,
            'category_id' => $s1->id,
            'name' => 'Standard Loitta Shutki',
            'slug' => 'loitta-shutki',
            'price' => 550,
            'weight' => 1.0,
            'stock' => 50,
            'is_featured' => true
        ]);
        ProductImage::create(['product_id' => $loitta->id, 'image_path' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?q=80&w=800', 'is_primary' => true]);

        // 5. Hero Slides
        HeroSlide::create([
            'site_id' => $acharu->id,
            'title' => 'Taste the Tradition',
            'subtitle' => 'Authentic handmade pickles.',
            'image_path' => 'https://images.unsplash.com/photo-1589135233689-d58620025983?q=80&w=1200',
            'order' => 1
        ]);

        HeroSlide::create([
            'site_id' => $tajashutki->id,
            'title' => 'Coastal Seafood Harvest',
            'subtitle' => '100% naturally sun-dried seafood.',
            'image_path' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?q=80&w=1200',
            'order' => 1
        ]);
    }
}
