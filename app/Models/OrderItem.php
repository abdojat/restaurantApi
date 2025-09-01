<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'dish_id',
        'quantity',
        'unit_price',
        'total_price',
        'special_instructions',
        'status'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the order that owns the order item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the dish for this order item.
     */
    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }

    /**
     * Scope to get pending items.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get preparing items.
     */
    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    /**
     * Scope to get ready items.
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /**
     * Scope to get served items.
     */
    public function scopeServed($query)
    {
        return $query->where('status', 'served');
    }

    /**
     * Check if item is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if item is preparing.
     */
    public function isPreparing()
    {
        return $this->status === 'preparing';
    }

    /**
     * Check if item is ready.
     */
    public function isReady()
    {
        return $this->status === 'ready';
    }

    /**
     * Check if item is served.
     */
    public function isServed()
    {
        return $this->status === 'served';
    }

    /**
     * Check if item can be updated by cashier.
     */
    public function canBeUpdatedByCashier()
    {
        return in_array($this->status, ['pending', 'preparing', 'ready']);
    }

    /**
     * Get formatted unit price.
     */
    public function getFormattedUnitPriceAttribute()
    {
        return '$' . number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total price.
     */
    public function getFormattedTotalPriceAttribute()
    {
        return '$' . number_format($this->total_price, 2);
    }
}
