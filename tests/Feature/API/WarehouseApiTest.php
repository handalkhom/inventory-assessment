<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_view_warehouse_stock(): void
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->actingAs($this->user)->getJson('/api/v1/warehouses/' . $warehouse->id . '/stock');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'location', 'capacity_m3', 'products' => []
            ]
        ]);
        $this->assertEquals($warehouse->id, $response->json('data.id'));
    }

    public function test_returns_404_for_invalid_warehouse_id(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/warehouses/9999/stock');
        $response->assertStatus(404);
    }
}
