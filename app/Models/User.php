<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $connection = 'mongodb';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->username ??= 'user_'.strtolower((string) Str::ulid());
            if (! array_key_exists('roles', $user->getAttributes())) {
                $user->setAttribute('roles', ['User']);
            }
            $user->is_active ??= true;
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function getOwnerObjectIdAttribute(): ?ObjectId
    {
        return $this->id ? new ObjectId($this->id) : null;
    }

    public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id', 'owner_object_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'owner_object_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    // Helpers
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getAttributes()['roles'] ?? [], true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }
}
