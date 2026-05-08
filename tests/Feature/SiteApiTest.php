<?php

namespace Tests\Feature;

use Tests\TestCase;

class SiteApiTest extends TestCase
{
    public function test_init_returns_valid_structure(): void
    {
        $response = $this->getJson('/api/v1/tajashutki/init');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'site',
                'categories',
                'products',
                'slides',
                'settings',
                'contact',
                'reviews',
            ]
        ]);
    }

    public function test_version_returns_integer(): void
    {
        $response = $this->getJson('/api/v1/tajashutki/version');

        $response->assertStatus(200);
        $response->assertJsonStructure(['version']);
        $this->assertIsInt($response->json('version'));
    }

    public function test_products_endpoint_returns_list(): void
    {
        $response = $this->getJson('/api/v1/tajashutki/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => ['data', 'current_page', 'last_page', 'total']
        ]);
    }

    public function test_reviews_endpoint_returns_list(): void
    {
        $response = $this->getJson('/api/v1/tajashutki/reviews');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data'
        ]);
    }

    public function test_contact_endpoint_validates_input(): void
    {
        $response = $this->postJson('/api/v1/tajashutki/contact', []);

        $response->assertStatus(422);
    }

    public function test_track_order_returns_404_for_invalid_id(): void
    {
        $response = $this->getJson('/api/v1/tajashutki/orders/track/invalid-tracking-123');

        $response->assertStatus(404);
    }

    public function test_acharu_site_also_works(): void
    {
        $response = $this->getJson('/api/v1/acharu/version');
        $response->assertStatus(200);
        $response->assertJsonStructure(['version']);
    }
}
