<?php

namespace App\Models;

use App\Models\Store;
use App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'product_id',
        'stock',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
