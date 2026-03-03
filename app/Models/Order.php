<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'status',
        'total_cents',
        'name',
        'phone',
        'email',
        'delivery',
        'address',
        'notes',
        'mp_preference_id',
        'mp_payment_id',
        'mp_status',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
