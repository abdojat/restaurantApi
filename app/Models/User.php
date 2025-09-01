<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role_id',
        'avatar_path',
        'address',
        'date_of_birth',
        'preferred_locale',
        'is_banned',
        'banned_at',
        'failed_deliveries_count'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'banned_at' => 'datetime',
            'is_banned' => 'boolean',
        ];
    }

    /**
     * Get the user's primary role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get all roles for the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get the user's reservations.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the user's orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the user's favorite dishes.
     */
    public function favoriteDishes()
    {
        return $this->belongsToMany(Dish::class, 'user_dish_favorites')->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }
        return $this->roles()->where('id', $role->id)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }
        
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is manager.
     */
    public function isManager()
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user is cashier.
     */
    public function isCashier()
    {
        return $this->hasRole('cashier');
    }

    /**
     * Check if user is customer.
     */
    public function isCustomer()
    {
        return $this->hasRole('customer');
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers()
    {
        return $this->isAdmin() || $this->isManager();
    }

    /**
     * Check if user can manage menu.
     */
    public function canManageMenu()
    {
        return $this->isAdmin() || $this->isManager();
    }

    /**
     * Check if user can manage orders.
     */
    public function canManageOrders()
    {
        return $this->isAdmin() || $this->isManager() || $this->isCashier();
    }

    /**
     * Check if user is banned.
     */
    public function isBanned()
    {
        return (bool) $this->is_banned;
    }
}
