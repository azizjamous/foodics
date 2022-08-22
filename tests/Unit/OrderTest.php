<?php

namespace Tests\Unit;

use App\Mail\IngredientsLow;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_if_seeder_works()
    {
        $this->seed();

        $this->assertDatabaseHas('products', [
            'name' => 'Burger'
        ]);

        $this->assertDatabaseHas('ingredients', [
            'name' => 'Cheese'
        ]);

        $this->assertDatabaseHas('product_ingredients', [
            'ingredient_id' => '3',
            'required_amount' => 20,
        ]);
    }

    public function test_if_required_amount_is_calculated()
    {
        $ingredientId = 1;
        $quantity = 2;
        $ingredient = Ingredient::whereKey($ingredientId)->with('ingredient')->first();

        $response = $this->post("/api/orders", [
            'products' => [[
                'product_id' => 1,
                'quantity' => $quantity,
            ]],
        ]);

        $requiredAmount = $ingredient->ingredient->required_amount * $quantity;
        $newAmount = $ingredient->available_amount - $requiredAmount;

        $updatedIngredient = Ingredient::whereKey($ingredientId)->with('ingredient')->first();

        $this->assertEquals($updatedIngredient->available_amount, $newAmount);
    }

    public function test_if_low_inventory_email_is_sent()
    {
        Mail::fake();

        $ingredientId = 3;
        $quantity = 30;

        $response = $this->post("/api/orders", [
            'products' => [[
                'product_id' => 1,
                'quantity' => $quantity,
            ]],
        ]);

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredientId,
            'is_notified' => 1,
        ]);

        $response = $this->post("/api/orders", [
            'products' => [[
                'product_id' => 1,
                'quantity' => 1,
            ]],
        ]);

        Mail::assertSent(IngredientsLow::class, 1);
    }
}
