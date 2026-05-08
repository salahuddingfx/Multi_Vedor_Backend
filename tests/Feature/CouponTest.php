<?php

namespace Tests\Feature;

use Tests\TestCase;

class CouponTest extends TestCase
{
    public function test_validate_coupon_returns_error_for_invalid_code(): void
    {
        $response = $this->postJson('/api/v1/tajashutki/validate-coupon', [
            'code' => 'NONEXISTENT123',
            'items' => [],
            'customer_phone' => '01700000000'
        ]);

        $response->assertStatus(404);
        $response->assertJsonStructure(['error']);
    }

    public function test_validate_coupon_requires_code(): void
    {
        $response = $this->postJson('/api/v1/tajashutki/validate-coupon', [
            'items' => [],
            'customer_phone' => '01700000000'
        ]);

        $response->assertStatus(422);
    }

    public function test_validate_coupon_works_for_acharu(): void
    {
        $response = $this->postJson('/api/v1/acharu/validate-coupon', [
            'code' => 'INVALIDCODE',
            'items' => [],
            'customer_phone' => '01700000000'
        ]);

        $this->assertContains($response->status(), [404, 422]);
    }
}
