<?php

namespace Tests\Feature\API;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_fetch_aggregated_stock_report(): void
    {
        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();

        $product1 = Product::factory()->create(['unit_price' => 100.00]);
        $product2 = Product::factory()->create(['unit_price' => 50.00]);

        $warehouse1->products()->attach($product1->id, ['quantity_on_hand' => 10]); 
        $warehouse1->products()->attach($product2->id, ['quantity_on_hand' => 5]);  
                                                                                    

        $warehouse2->products()->attach($product1->id, ['quantity_on_hand' => 2]); 
                                                                                   

        \Illuminate\Support\Facades\Artisan::call('stock:refresh-summaries');

        $response = $this->actingAs($this->user)->getJson('/api/v1/stock-report');

        $response->assertStatus(200);
        
        $data = collect($response->json());
        
        $wh1Data = $data->firstWhere('id', $warehouse1->id);
        $wh2Data = $data->firstWhere('id', $warehouse2->id);

        $this->assertNotNull($wh1Data);
        $this->assertEquals(1250.0, $wh1Data['total_stock_value']);

        $this->assertNotNull($wh2Data);
        $this->assertEquals(200.0, $wh2Data['total_stock_value']);
    }
}
