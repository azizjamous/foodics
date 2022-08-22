<?php

namespace App\Http\Controllers;

use App\Mail\IngredientsLow;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $products = $request->products;
        $orderUuid = Str::orderedUuid();
        $order = [];
        $ingredients = [];

        foreach ($products as $product) {
            $productId = $product['product_id'];
            $quantity = $product['quantity'];

            $ingredients[$productId] = Product::find($productId)->ingredients()->get([
                'ingredients.id', 'name', 'available_amount', 'max_amount', 'is_notified'
            ]);

            $timestamp = Carbon::now();
            $order[] = [
                'uuid' => $orderUuid,
                'product_id' => $productId,
                'quantity' => $quantity,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach ($ingredients as $productIngredients) {
            $enoughIngredients = $this->checkIfEnoughIngredients($productIngredients, $quantity);

            if (!$enoughIngredients) {
                return response()->json("Not enough ingredients to complete the order", 422);
            }

            foreach ($productIngredients as $ingredient) {
                $requiredAmount = $ingredient->pivot->required_amount * $quantity;
                $ingredient->available_amount -= $requiredAmount;
                $ingredient->save();

                $this->checkIfLowInventory($ingredient);
            }
        }

        Order::insert($order);

        return response()->json("Order processed");
    }

    private function checkIfEnoughIngredients($productIngredients, $quantity)
    {
        foreach ($productIngredients as $ingredient) {
            $requiredAmount = $ingredient->pivot->required_amount * $quantity;

            if ($ingredient->available_amount < $requiredAmount) {
                return false;
            }
        }

        return true;
    }

    private function checkIfLowInventory($ingredient)
    {
        $halfAmount = $ingredient->max_amount / 2;

        if (!$ingredient->is_notified && $ingredient->available_amount < $halfAmount) {
            // Queue emails for faster code execution
            Mail::to('manager@restaurant.com')->send(new IngredientsLow($ingredient));
            $ingredient->is_notified = true;
            $ingredient->save();
        }
    }
}
