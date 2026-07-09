<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $endpoints = [
            ['GET', '/api/v1/products'],
            ['GET', '/api/v1/products/DUMMY-SKU'],
            ['GET', '/api/v1/warehouses/1/stock'],
            ['POST', '/api/v1/stock-movements'],
            ['GET', '/api/v1/stock-report'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(401);
            $response->assertJson(['message' => 'Unauthenticated.']);
        }
    }
}
