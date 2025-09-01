<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'table_id',
        'reservation_date',
        'start_date',
        'end_date',
        'guests',
        'status',
        'special_requests',
        'contact_phone',
        'contact_email',
        'notes'
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the user that owns the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the table for this reservation.
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Scope to get pending reservations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get confirmed reservations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope to get cancelled reservations.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope to get completed reservations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get reservations for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('reservation_date', $date);
    }

    /**
     * Scope to get upcoming reservations.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>', now())
            ->whereIn('status', ['pending', 'confirmed']);
    }

    /**
     * Check if reservation is confirmed.
     */
    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    /**
     * Scope: active (not cancelled) and not expired by end_date.
     */
    public function scopeActiveNotExpired($query)
    {
        return $query->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            });
    }

    /**
     * Scope: reservations whose end_date has passed.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('end_date')
            ->where('end_date', '<=', now());
    }

    /**
     * Check if reservation is cancelled.
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if reservation is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if reservation is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
}
