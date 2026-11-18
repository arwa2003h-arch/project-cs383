<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name_en', 'name_ar', 'unit_price', 'is_active'];

    // A service has many orders (Order model is made by Mariah)
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
