<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Cart extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = ['user_id', 'items'];

    protected $attributes = ['items' => []];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
