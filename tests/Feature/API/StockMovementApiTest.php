<?php

namespace Tests\Feature\API;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_validates_required_fields_and_formats(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/stock-movements', []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_sku', 'warehouse_id', 'movement_type', 'quantity', 'moved_by']);
    }

    public function test_can_create_valid_stock_movement(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $payload = [
            'product_sku' => $product->sku,
            'warehouse_id' => $warehouse->id,
            'movement_type' => 'in',
            'quantity' => 50,
            'moved_by' => 'API User'
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/stock-movements', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'product' => ['id', 'sku', 'name'],
                'warehouse' => ['id', 'name'],
                'movement_type',
                'quantity',
                'moved_by',
                'created_at'
            ]
        ]);
        
        $this->assertEquals(50, $response->json('data.quantity'));
    }

    public function test_domain_validation_exceptions_are_returned_as_422_json(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        // Trying to move out stock that doesn't exist. This triggers a ValidationException
        // from the StockMovement model boot method (BR3). The API should catch it and return 422.
        $payload = [
            'product_sku' => $product->sku,
            'warehouse_id' => $warehouse->id,
            'movement_type' => 'out',
            'quantity' => 10,
            'moved_by' => 'API User'
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/stock-movements', $payload);

        $response->assertStatus(422);
        // Ensure standard validation format is returned by checking for 'message' and 'errors'
        $response->assertJsonStructure([
            'message',
            'errors'
        ]);
    }
}
