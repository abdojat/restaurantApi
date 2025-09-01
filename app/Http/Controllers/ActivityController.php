<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Get recent activities across orders and reservations.
     * Accessible by admin, manager, and cashier.
     */
    public function getRecentActivities(Request $request)
    {
        $limit = (int) $request->query('limit', 20);
        $limit = max(1, min($limit, 50));
        $since = $request->query('since');
        $types = $request->query('types', 'all'); // all | orders | reservations

        $includeOrders = $types === 'all' || $types === 'orders';
        $includeReservations = $types === 'all' || $types === 'reservations';

        $activities = collect();

        if ($includeOrders) {
            $ordersQuery = Order::with(['user:id,name', 'table:id,name'])
                ->orderBy('created_at', 'desc');

            if (!empty($since)) {
                $ordersQuery->where('created_at', '>=', $since);
            }

            $orders = $ordersQuery->take($limit)->get()->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'type' => 'order',
                    'title' => 'New order received',
                    'created_at' => $order->created_at,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'table' => optional($order->table)->name,
                    'user' => optional($order->user)->name,
                ];
            });

            $activities = $activities->merge($orders);
        }

        if ($includeReservations) {
            $reservationsQuery = Reservation::with(['user:id,name', 'table:id,name'])
                ->orderBy('created_at', 'desc');

            if (!empty($since)) {
                $reservationsQuery->where('created_at', '>=', $since);
            }

            $reservations = $reservationsQuery->take($limit)->get()->map(function (Reservation $reservation) {
                return [
                    'id' => $reservation->id,
                    'type' => 'reservation',
                    'title' => 'Reservation created',
                    'created_at' => $reservation->created_at,
                    'reservation_date' => $reservation->reservation_date,
                    'status' => $reservation->status,
                    'guests' => $reservation->guests,
                    'table' => optional($reservation->table)->name,
                    'user' => optional($reservation->user)->name,
                ];
            });

            $activities = $activities->merge($reservations);
        }

        $activities = $activities
            ->sortByDesc('created_at')
            ->values()
            ->take($limit);

        return response()->json([
            'activities' => $activities,
            'meta' => [
                'limit' => $limit,
                'types' => $types,
                'count' => $activities->count(),
            ],
        ]);
    }
}


