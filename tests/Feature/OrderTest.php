<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_order_controller()
    {
        $response = $this->post("/api/orders", [
            'products' => [[
                'product_id' => 1,
                'quantity' => 2,
            ]],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'product_id' => 1,
            'quantity' => 2
        ]);
    }
}
