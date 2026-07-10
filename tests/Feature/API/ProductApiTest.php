<?php

namespace Tests\Feature\API;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_products_with_pagination_and_filters(): void
    {
        Product::factory()->count(5)->create(['category' => 'finished_goods', 'is_active' => true]);
        Product::factory()->count(3)->create(['category' => 'raw_material', 'is_active' => true]);
        Product::factory()->count(2)->create(['category' => 'finished_goods', 'is_active' => false]);

        // Unfiltered
        $response = $this->actingAs($this->user)->getJson('/api/v1/products');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'sku', 'name', 'category', 'unit_price', 'is_active']
            ],
            'links',
            'meta'
        ]);
        $this->assertCount(10, $response->json('data'));

        // Filter by category
        $response = $this->actingAs($this->user)->getJson('/api/v1/products?category=finished_goods');
        $this->assertCount(7, $response->json('data'));

        // Filter by is_active
        $response = $this->actingAs($this->user)->getJson('/api/v1/products?is_active=1');
        $this->assertCount(8, $response->json('data'));

        // Filter by category and is_active
        $response = $this->actingAs($this->user)->getJson('/api/v1/products?category=finished_goods&is_active=1');
        $this->assertCount(5, $response->json('data'));
    }

    public function test_can_show_product_by_sku_with_stock_levels(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->getJson('/api/v1/products/' . $product->sku);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'sku', 'name', 'warehouses' => []
            ]
        ]);
        $this->assertEquals($product->sku, $response->json('data.sku'));
    }

    public function test_returns_404_for_invalid_sku(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/products/INVALID-SKU');
        $response->assertStatus(404);
    }
}
