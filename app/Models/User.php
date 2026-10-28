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

    protected $fillable = ['name', 'email', 'password', 'username'];

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

    public function roles()
    {
        return new class($this) {
            private User $user;

            public function __construct(User $user)
            {
                $this->user = $user;
            }

            public function attach(mixed $roleId): void
            {
                $role = Role::find($roleId);
                $roleName = $role ? $role->name : (string) $roleId;
                $currentRoles = (array) ($this->user->getAttributes()['roles'] ?? []);
                if (! in_array($roleName, $currentRoles, true)) {
                    $currentRoles[] = $roleName;
                }
                $this->user->forceFill(['roles' => $currentRoles])->save();
            }

            public function detach(mixed $roleId): void
            {
                $role = Role::find($roleId);
                $roleName = $role ? $role->name : (string) $roleId;
                $currentRoles = array_values(array_filter(
                    (array) ($this->user->getAttributes()['roles'] ?? []),
                    fn ($r) => $r !== $roleName
                ));
                $this->user->forceFill(['roles' => $currentRoles])->save();
            }
        };
    }

    // Helpers
    public function hasRole(string $role): bool
    {
        $roles = $this->getAttributes()['roles'] ?? [];
        if (in_array($role, (array) $roles, true)) {
            return true;
        }

        try {
            return $this->roles()->where('name', $role)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }
}
