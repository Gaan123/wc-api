<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'id',
        'product_id',
        'variation_id',
        'quantity',
        'tax_class',
        'subtotal',
        'subtotal_tax',
        'total',
        'total_tax',
        'meta_data',
        'taxes',
        'sku',
        'price',
    ];
    protected $casts=[
        'total_tax'=>'json',
        'meta_data'=>'json',
    ];
}
