<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_endpoints_enforce_rate_limit(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 60; $i++) {
            $response = $this->actingAs($user)->getJson('/api/v1/products');
            $response->assertStatus(200);
        }

        $response = $this->actingAs($user)->getJson('/api/v1/products');
        $response->assertStatus(429);
    }
}
