<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $acharu = Site::where('slug', 'acharu')->first();
        $tajashutki = Site::where('slug', 'tajashutki')->first();

        if (!$acharu || !$tajashutki) {
            $this->command->error('Sites not found. Please run DatabaseSeeder first.');
            return;
        }

        // Get or create categories
        $p1 = Category::firstOrCreate(['site_id' => $acharu->id, 'slug' => 'pickles'], ['name' => 'Pickles']);
        $p2 = Category::firstOrCreate(['site_id' => $acharu->id, 'slug' => 'sauces'], ['name' => 'Sauces']);
        
        $s1 = Category::firstOrCreate(['site_id' => $tajashutki->id, 'slug' => 'dry-fish'], ['name' => 'Dry Fish']);
        $s2 = Category::firstOrCreate(['site_id' => $tajashutki->id, 'slug' => 'seafood'], ['name' => 'Seafood']);

        // Acharu mock names
        $acharuNames = [
            'Mango', 'Garlic', 'Chili', 'Olive', 'Tamarind', 'Plum', 'Mixed Pickle', 'Lemon', 'Tomato', 'Amra'
        ];
        
        // TajaShutki mock names
        $shutkiNames = [
            'Loitta', 'Churi', 'Kachki', 'Mola', 'Poa', 'Fashya', 'Rupchanda', 'Keski', 'Shrimp', 'Crab'
        ];

        // Seed 25 products for Acharu
        for ($i = 1; $i <= 25; $i++) {
            $baseName = $acharuNames[array_rand($acharuNames)];
            $name = "Homemade {$baseName} Pickle - Batch #{$i}";
            $slug = Str::slug($name);
            
            // Avoid duplicates
            if (Product::where('slug', $slug)->exists()) {
                continue;
            }

            $product = Product::create([
                'site_id' => $acharu->id,
                'category_id' => ($i % 2 == 0) ? $p1->id : $p2->id,
                'name' => $name,
                'slug' => $slug,
                'price' => rand(150, 450),
                'weight' => rand(2, 8) / 10.0,
                'stock' => rand(10, 150),
                'is_featured' => ($i % 5 == 0)
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'https://images.unsplash.com/photo-1589135233689-d58620025983?q=80&w=800',
                'is_primary' => true
            ]);
        }

        // Seed 25 products for TajaShutki
        for ($i = 1; $i <= 25; $i++) {
            $baseName = $shutkiNames[array_rand($shutkiNames)];
            $name = "Premium {$baseName} Shutki - Grade A #{$i}";
            $slug = Str::slug($name);

            // Avoid duplicates
            if (Product::where('slug', $slug)->exists()) {
                continue;
            }

            $product = Product::create([
                'site_id' => $tajashutki->id,
                'category_id' => ($i % 2 == 0) ? $s1->id : $s2->id,
                'name' => $name,
                'slug' => $slug,
                'price' => rand(300, 1200),
                'weight' => rand(5, 20) / 10.0,
                'stock' => rand(5, 80),
                'is_featured' => ($i % 5 == 0)
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?q=80&w=800',
                'is_primary' => true
            ]);
        }

        $this->command->info('Successfully seeded 50 products!');
    }
}
