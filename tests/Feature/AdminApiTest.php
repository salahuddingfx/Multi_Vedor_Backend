<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminApiTest extends TestCase
{
    public function test_admin_login_requires_credentials(): void
    {
        $response = $this->postJson('/api/admin/login', []);

        $response->assertStatus(422);
    }

    public function test_admin_me_returns_unauthorized(): void
    {
        $response = $this->getJson('/api/admin/me');

        $response->assertStatus(401);
    }

    public function test_admin_stats_returns_unauthorized(): void
    {
        $response = $this->getJson('/api/admin/stats');

        $response->assertStatus(401);
    }
}
