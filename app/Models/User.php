<?php

namespace App\Models;

use App\Models\Store;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

use Filament\Models\Contracts\FilamentUser;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles;
    use HasPanelShield;
    use HasFactory, Notifiable;

    protected $fillable = [
        'store_id',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // The relationship roles is already defined in the HasRoles trait
    // public function roles()
    // {
    //     return $this->belongsTo(Role::class);
    // }
}
