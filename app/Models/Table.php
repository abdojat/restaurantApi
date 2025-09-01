<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'capacity',
        'type',
        'status',
        'description',
        'description_ar',
        'is_active',
        'image_path'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'reservations_list',
    ];

    /**
     * Get the reservations for this table.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get active (non-cancelled, not expired) reservations for this table ordered by start time.
     */
    public function activeReservations()
    {
        return $this->hasMany(Reservation::class)
            ->activeNotExpired()
            ->orderByRaw('COALESCE(start_date, reservation_date) ASC');
    }

    /**
     * Accessor: list of reservations entries with customer_id, start_date, end_date.
     */
    public function getReservationsListAttribute()
    {
        return $this->activeReservations
            ->map(function ($reservation) {
                return [
                    'customer_id' => $reservation->user_id,
                    'start_date' => $reservation->start_date,
                    'end_date' => $reservation->end_date,
                ];
            })->values();
    }

    /**
     * Get the orders for this table.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if table is available for a specific time.
     */
    public function isAvailableForTime($dateTime, $guests = null)
    {
        return $this->isAvailableForRange($dateTime, $dateTime, $guests);
    }

    /**
     * Get available tables for a specific time and guest count.
     */
    public static function getAvailableTables($dateTime, $guests = 1)
    {
        return static::getAvailableTablesForRange($dateTime, $dateTime, $guests);
    }

    /**
     * Check availability for a datetime range.
     */
    public function isAvailableForRange($startDateTime, $endDateTime, $guests = null)
    {
        if (!$this->is_active || $this->status !== 'available') {
            return false;
        }

        if ($guests && $this->capacity < $guests) {
            return false;
        }

        $hasOverlap = $this->reservations()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query
                    // New reservations with [start_date, end_date)
                    ->where(function ($q) use ($startDateTime, $endDateTime) {
                        $q->whereNotNull('start_date')
                            ->whereNotNull('end_date')
                            ->where('start_date', '<', $endDateTime)
                            ->where('end_date', '>', $startDateTime);
                    })
                    // Backward compatibility: legacy single reservation_date equals start
                    ->orWhere(function ($q) use ($startDateTime) {
                        $q->whereNull('start_date')
                            ->whereNull('end_date')
                            ->where('reservation_date', $startDateTime);
                    });
            })
            ->exists();

        return !$hasOverlap;
    }

    /**
     * Get available tables for a datetime range and guest count.
     */
    public static function getAvailableTablesForRange($startDateTime, $endDateTime, $guests = 1)
    {
        return static::where('is_active', true)
            ->where('status', 'available')
            ->where('capacity', '>=', $guests)
            ->whereDoesntHave('reservations', function ($query) use ($startDateTime, $endDateTime) {
                $query->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($startDateTime, $endDateTime) {
                        $q
                            ->where(function ($q2) use ($startDateTime, $endDateTime) {
                                $q2->whereNotNull('start_date')
                                    ->whereNotNull('end_date')
                                    ->where('start_date', '<', $endDateTime)
                                    ->where('end_date', '>', $startDateTime);
                            })
                            ->orWhere(function ($q2) use ($startDateTime) {
                                $q2->whereNull('start_date')
                                    ->whereNull('end_date')
                                    ->where('reservation_date', $startDateTime);
                            });
                    });
            })
            ->get();
    }
}
