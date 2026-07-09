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
        // Sanctum will authenticate using token, we simulate hitting the endpoint heavily
        // We need to bypass the fact that actingAs might bypass normal auth mechanisms,
        // but Laravel's rate limiter generally uses the authenticated user's ID or IP.
        
        // As per the requirement, it's 60 requests per minute. 
        // We will make 60 successful requests, and the 61st should fail.
        
        for ($i = 0; $i < 60; $i++) {
            $response = $this->actingAs($user)->getJson('/api/v1/products');
            $response->assertStatus(200);
        }

        // 61st request should be rate limited
        $response = $this->actingAs($user)->getJson('/api/v1/products');
        $response->assertStatus(429);
    }
}
