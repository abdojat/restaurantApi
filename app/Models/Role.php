<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description'
    ];

    /**
     * Get the users that have this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Check if role is admin
     */
    public function isAdmin()
    {
        return $this->name === 'admin';
    }

    /**
     * Check if role is manager
     */
    public function isManager()
    {
        return $this->name === 'manager';
    }

    /**
     * Check if role is cashier
     */
    public function isCashier()
    {
        return $this->name === 'cashier';
    }

    /**
     * Check if role is customer
     */
    public function isCustomer()
    {
        return $this->name === 'customer';
    }
}
