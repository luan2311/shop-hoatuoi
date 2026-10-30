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

    public const STATUS_LABELS = [
        'Pending' => 'Chờ xử lý',
        'Processing' => 'Đang đóng gói',
        'Shipped' => 'Đang giao hàng',
        'Completed' => 'Hoàn thành',
        'Cancelled' => 'Đã hủy',
    ];

    public const TRANSITIONS = [
        'Pending' => ['Processing', 'Cancelled'],
        'Processing' => ['Shipped', 'Cancelled'],
        'Shipped' => ['Completed'],
        'Completed' => [],
        'Cancelled' => [],
    ];

    protected function casts(): array
    {
        return ['order_date' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function canCustomerCancel(): bool
    {
        // COD is unpaid until collection. Banking has no verified payment feed.
        return $this->status === 'Pending' && $this->payment_method === 'COD'
            && ! $this->paid_at && ! $this->is_paid && ! $this->paid
            && in_array($this->payment_status, [null, 'unpaid', 'Unpaid'], true);
    }

    public function getTotalAttribute(): int|float
    {
        return $this->attributes['total'] ?? $this->attributes['total_amount'] ?? 0;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
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
