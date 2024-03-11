<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'order_key', 'status', 'date_created', 'total', 'customer_id', 'customer_note',
        'billing', 'shipping',];
    protected $casts = [
        'billing'=>'json', 'shipping'=>'json'
    ];

    public function lineItems():HasMany
    {
        return $this->hasMany(LineItem::class);
    }
}
