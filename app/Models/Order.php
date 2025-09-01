<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'table_id',
        'order_number',
        'status',
        'type',
        'subtotal',
        'tax_amount',
        'total_amount',
        'notes',
        'special_instructions',
        'estimated_ready_time'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'estimated_ready_time' => 'datetime',
    ];

    /**
     * Boot method to automatically generate order number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . date('Ymd') . '-' . Str::random(6);
            }
        });
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the table for this order.
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get the order items for this order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to get received orders.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope to get preparing orders.
     */
    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    /**
     * Scope to get orders with courier.
     */
    public function scopeWithCourier($query)
    {
        return $query->where('status', 'with_courier');
    }

    /**
     * Scope to get orders out for delivery.
     */
    public function scopeOutForDelivery($query)
    {
        return $query->where('status', 'out_for_delivery');
    }

    /**
     * Scope to get delivered orders.
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope to get failed delivery orders.
     */
    public function scopeDeliveryFailed($query)
    {
        return $query->where('status', 'delivery_failed');
    }

    /**
     * Scope to get cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope to get orders by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get orders for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Status helpers
     */
    public function isReceived()
    {
        return $this->status === 'received';
    }

    public function isPreparing()
    {
        return $this->status === 'preparing';
    }

    public function isWithCourier()
    {
        return $this->status === 'with_courier';
    }

    public function isOutForDelivery()
    {
        return $this->status === 'out_for_delivery';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isDeliveryFailed()
    {
        return $this->status === 'delivery_failed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if order can be updated by cashier.
     */
    public function canBeUpdatedByCashier()
    {
        return !in_array($this->status, ['delivered', 'delivery_failed', 'cancelled']);
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotalAttribute()
    {
        return '$' . number_format($this->subtotal, 2);
    }
}
