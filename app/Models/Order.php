<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    use Prunable;

    protected $fillable = ['number', 'order_key', 'status',  'total', 'customer_id', 'customer_note',
        'billing', 'shipping','created_at','updated_at'];
    protected $casts = [
        'billing'=>'json', 'shipping'=>'json'
    ];
    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('updated_at', '<=', now()->subMonths(3));
    }
    public function lineItems():HasMany
    {
        return $this->hasMany(LineItem::class);
    }

    public function scopeStatus($query,$value){
        $query->where('status',$value);
    }
    public function scopeOrderKey($query,$value){
        $query->where('order_key', 'LIKE', '%' . $value . '%');
    }

    public function scopeCustomer($query,$value){
        $value =strtolower($value);
        $query->whereRaw('LOWER(CONCAT(billing->>"$.first_name", " ", billing->>"$.last_name")) LIKE ?', ["%{$value}%"]);

    }
}
