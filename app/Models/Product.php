<?php

namespace App\Models;

use App\Models\ProductSize;
use App\Models\ProductCategory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
    ];

    public function product_categories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }
}
