<?php

namespace Tests\Feature;

use Tests\TestCase;

class CouponTest extends TestCase
{
    public function test_validate_coupon_requires_code(): void
    {
        $response = $this->postJson('/api/v1/tajashutki/validate-coupon', [
            'items' => [],
            'customer_phone' => '01700000000'
        ]);

        $response->assertStatus(422);
    }
}
