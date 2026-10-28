<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'order_code', 'checkout_key', 'user_id', 'customer', 'items', 'total_amount',
        'status', 'payment_method', 'order_date',
    ];

    const STATUSES = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];

    public const TRANSITIONS = [
        'Pending' => ['Processing', 'Cancelled'],
        'Processing' => ['Shipped', 'Cancelled'],
        'Shipped' => ['Completed'],
        'Completed' => [],
        'Cancelled' => [],
    ];

    protected function casts(): array
    {
        return ['order_date' => 'datetime'];
    }

    public function getTotalAttribute(): int|float
    {
        return $this->attributes['total'] ?? $this->attributes['total_amount'] ?? 0;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'Pending' => 'warning',
            'Processing' => 'info',
            'Shipped' => 'primary',
            'Completed' => 'success',
            'Cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
