<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Contact extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = ['code', 'full_name', 'email', 'phone', 'message', 'status'];

    protected $attributes = ['status' => 'pending'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($contact) {
            $contact->code ??= static::nextCode();
        });
    }

    // Mã liên hệ kế tiếp: CT001, CT002, ... (code là unique trong collection contacts)
    public static function nextCode(): string
    {
        $last = static::where('code', 'regex', '/^CT\d+$/')->orderBy('code', 'desc')->value('code');
        $number = $last ? (int) substr($last, 2) + 1 : 1;

        return 'CT' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }
}
