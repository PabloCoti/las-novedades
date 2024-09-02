<?php

namespace App\Models;

use App\Models\User;
use App\Models\Store;
use App\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'customer_id',
        'date',
        'total',
    ];

    protected $casts = [
        'date'    => 'datetime',
        'details' => 'json',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
