<?php

namespace App\Models;

use App\Models\Category;
use App\Models\ProductSize;
use App\Models\ProductColor;
use App\Models\ProductStockStore;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'description',
        'price',
        'special_price',
    ];

    protected $appends = [
        'category_name',
    ];

    public function getCategoryNameAttribute()
    {
        return $this->product_category->name;
    }

    public function getCategoryDescriptionAttribute()
    {
        $result  = $this->product_category->name;
        $result .= !empty($this->description)
            ? ' - ' . $this->description
            : '';
        return $result;
    }

    public function getSizesAttribute()
    {
        $sizes = '';

        $this->product_sizes->sortBy(function ($product_size)
        {
            return $product_size->size->id;
        })->each(function ($product_size) use (&$sizes)
        {
            $sizes .= $product_size->size->name . ', ';
        });

        return rtrim($sizes, ', ');
    }

    public function getColorsAttribute()
    {
        $colors = '';

        $this->product_colors->sortBy(function ($product_color)
        {
            return $product_color->color->id;
        })->each(function ($product_color) use (&$colors)
        {
            $colors .= $product_color->color->name . ', ';
        });

        return rtrim($colors, ', ');
    }

    public function product_category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function product_colors()
    {
        return $this->hasMany(ProductColor::class);
    }

    public function product_stock_stores()
    {
        return $this->hasMany(ProductStockStore::class);
    }

    public function product_sizes()
    {
        return $this->hasMany(ProductSize::class);
    }
}
