<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients')
            ->withPivot('required_amount');
    }

    public function ingredient()
    {
        return $this->hasOne(ProductIngredient::class, 'ingredient_id', 'id');
    }
}
