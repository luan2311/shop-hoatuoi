<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'total', 'receiver_name', 'receiver_address',
        'receiver_phone', 'email', 'note', 'status', 'payment_method',
    ];

    const STATUSES = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'Pending'    => 'warning',
            'Processing' => 'info',
            'Shipped'    => 'primary',
            'Completed'  => 'success',
            'Cancelled'  => 'danger',
            default      => 'secondary',
        };
    }
}
