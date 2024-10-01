<?php

namespace App\Models;

use App\Models\Sale;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'active',
        'special',
        'name',
        'email',
        'phone',
        'tributary_number',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
