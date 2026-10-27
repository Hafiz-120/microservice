<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class Order extends Model
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PAID = 'PAID';
 
    protected $fillable = ['user_id', 'status', 'total_amount'];
 
    protected $casts = [
        'user_id' => 'integer',
        'total_amount' => 'integer',
    ];
 
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
 
    // Format tanggal di JSON: 2026-10-20T03:15:00+00:00 (sesuai contoh soal)
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('c');
    }
}

