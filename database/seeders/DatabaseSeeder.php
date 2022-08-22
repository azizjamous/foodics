<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductIngredient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        ProductIngredient::truncate();
        Product::truncate();
        Ingredient::truncate();
        Schema::enableForeignKeyConstraints();

        $product = Product::create([
            'name' => 'Burger',
        ]);

        ProductIngredient::create([
            'product_id' => $product->id,
            'ingredient_id' => Ingredient::create([
                'name' => 'Beef',
                'available_amount' => 20000,
                'max_amount' => 20000,
                'is_notified' => false,
            ])->id,
            'required_amount' => 150,
        ]);
        ProductIngredient::create([
            'product_id' => $product->id,
            'ingredient_id' => Ingredient::create([
                'name' => 'Cheese',
                'available_amount' => 5000,
                'max_amount' => 5000,
                'is_notified' => false,
            ])->id,
            'required_amount' => 30,
        ]);
        ProductIngredient::create([
            'product_id' => $product->id,
            'ingredient_id' => Ingredient::create([
                'name' => 'Onion',
                'available_amount' => 1000,
                'max_amount' => 1000,
                'is_notified' => false,
            ])->id,
            'required_amount' => 20,
        ]);
    }
}
