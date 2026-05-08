<?php

namespace Tests\Feature;

use Tests\TestCase;

class SiteApiTest extends TestCase
{
    public function test_contact_endpoint_validates_input(): void
    {
        $response = $this->postJson('/api/v1/tajashutki/contact', []);

        $response->assertStatus(422);
    }

    public function test_review_store_validates_input(): void
    {
        $response = $this->postJson('/api/v1/tajashutki/reviews', []);

        $response->assertStatus(422);
    }
}
