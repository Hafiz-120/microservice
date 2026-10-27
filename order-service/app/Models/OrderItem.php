<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class OrderItem extends Model
{
    public $timestamps = false;
 
    protected $fillable = [
        'order_id', 'product_id', 'product_name',
        'unit_price', 'quantity', 'subtotal',
    ];
 
    // id dan order_id tidak ditampilkan agar bentuk JSON sama dengan contoh soal
    protected $hidden = ['id', 'order_id'];
 
    protected $casts = [
        'product_id' => 'integer',
        'unit_price' => 'integer',
        'quantity' => 'integer',
        'subtotal' => 'integer',
    ];
 
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
