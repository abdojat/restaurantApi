<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Reservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Get available tables for a specific time and guest count.
     */
    public function getAvailableTables(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date', 'after:now'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $start = $request->start_date;
        $end = $request->end_date ?? $request->start_date;

        $availableTables = Table::getAvailableTablesForRange($start, $end, $request->guests);
        
        return response()->json([
            'available_tables' => $availableTables
        ]);
    }

    /**
     * Create a table reservation.
     */
    public function createReservation(Request $request)
    {
        $data = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'reservation_date' => ['nullable', 'date', 'after:now'],
            'start_date' => ['nullable', 'date', 'after:now'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
            'special_requests' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email'],
        ]);

        $user = auth()->user();
        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            return response()->json([
                'message' => 'Your account is banned from creating orders due to repeated delivery failures.'
            ], 403);
        }
        $table = Table::findOrFail($data['table_id']);

        // Determine reservation time window (support legacy single reservation_date)
        $startDate = $data['start_date'] ?? $data['reservation_date'] ?? null;
        $endDate = $data['end_date'] ?? ($startDate ?? null);

        if (!$startDate) {
            return response()->json([
                'message' => 'Either start_date/end_date or reservation_date must be provided.'
            ], 422);
        }

        // Check if table is available for the requested range
        if (!$table->isAvailableForRange($startDate, $endDate, $data['guests'])) {
            return response()->json([
                'message' => 'Table is not available for the requested time range and guest count.'
            ], 400);
        }

        // Check table capacity
        if ($table->capacity < $data['guests']) {
            return response()->json([
                'message' => 'Table capacity is insufficient for the requested guest count.'
            ], 400);
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'table_id' => $data['table_id'],
            'reservation_date' => $startDate,
            'start_date' => $request->filled('start_date') ? $startDate : null,
            'end_date' => $request->filled('start_date') ? $endDate : null,
            'guests' => $data['guests'],
            'status' => 'pending',
            'special_requests' => $data['special_requests'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? $user->phone_number,
            'contact_email' => $data['contact_email'] ?? $user->email,
        ]);

        return response()->json([
            'message' => 'Reservation created successfully. Awaiting confirmation.',
            'reservation' => $reservation->load(['table'])
        ], 201);
    }

    /**
     * Get user's reservations.
     */
    public function getMyReservations(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->reservations()->with('table');
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Prefer ordering by start_date if present
        $reservations = $query->orderByRaw('COALESCE(start_date, reservation_date) DESC')->paginate(15);
        
        return response()->json([
            'reservations' => $reservations
        ]);
    }

    /**
     * Cancel a reservation.
     */
    public function cancelReservation($id)
    {
        $user = auth()->user();
        $reservation = $user->reservations()->findOrFail($id);
        
        if ($reservation->status === 'cancelled') {
            return response()->json([
                'message' => 'Reservation is already cancelled.'
            ], 400);
        }
        
        if ($reservation->status === 'completed') {
            return response()->json([
                'message' => 'Cannot cancel completed reservation.'
            ], 400);
        }

        $reservation->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Reservation cancelled successfully.',
            'reservation' => $reservation->load('table')
        ]);
    }

    /**
     * Get available dishes for ordering.
     */
    public function getAvailableDishes(Request $request)
    {
        $query = Dish::with('category')->available();
        
        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by dietary preferences
        if ($request->boolean('vegetarian')) {
            $query->vegetarian();
        }
        
        if ($request->boolean('vegan')) {
            $query->vegan();
        }
        
        if ($request->boolean('gluten_free')) {
            $query->glutenFree();
        }
        
        $dishes = $query->ordered()->get();
        
        return response()->json([
            'dishes' => $dishes
        ]);
    }

    /**
     * Create a new order.
     */
    public function createOrder(Request $request)
    {
        $data = $request->validate([
            'table_id' => ['nullable', 'exists:tables,id'],
            'type' => ['required', 'in:dine_in,takeaway,delivery'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.dish_id' => ['required', 'exists:dishes,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'items.*.special_instructions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'special_instructions' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        
        // Validate dishes are available
        $dishIds = collect($data['items'])->pluck('dish_id');
        $availableDishes = Dish::whereIn('id', $dishIds)->available()->pluck('id');
        
        if ($availableDishes->count() !== $dishIds->count()) {
            return response()->json([
                'message' => 'Some selected dishes are not available.'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Calculate totals
            $subtotal = 0;
            $orderItems = [];
            
            foreach ($data['items'] as $item) {
                $dish = Dish::find($item['dish_id']);
                $itemTotal = $dish->price * $item['quantity'];
                $subtotal += $itemTotal;
                
                $orderItems[] = [
                    'dish_id' => $item['dish_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $dish->price,
                    'total_price' => $itemTotal,
                    'special_instructions' => $item['special_instructions'] ?? null,
                ];
            }
            
            // Calculate tax (assuming 10% tax rate)
            $taxRate = 0.10;
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $taxAmount;
            
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'table_id' => $data['table_id'] ?? null,
                'type' => $data['type'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'special_instructions' => $data['special_instructions'] ?? null,
                'status' => 'received',
            ]);
            
            // Create order items
            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order->load(['orderItems.dish', 'table'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get user's orders.
     */
    public function getMyOrders(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->orders()->with(['orderItems.dish', 'table']);
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'orders' => $orders
        ]);
    }

    /**
     * Get a specific order.
     */
    public function getOrder($id)
    {
        $user = auth()->user();
        $order = $user->orders()->with(['orderItems.dish', 'table'])->findOrFail($id);
        
        return response()->json([
            'order' => $order
        ]);
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder($id)
    {
        $user = auth()->user();
        $order = $user->orders()->findOrFail($id);

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'Order is already cancelled.'
            ], 400);
        }

        // Allow customers to cancel only before preparation starts
        if (!$order->isReceived()) {
            return response()->json([
                'message' => 'Order cannot be cancelled at this stage.'
            ], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'order' => $order->fresh()->load(['orderItems.dish', 'table'])
        ]);
    }

    /**
     * Get order tracking information.
     */
    public function trackOrder($id)
    {
        $user = auth()->user();
        $order = $user->orders()->with(['orderItems.dish', 'table'])->findOrFail($id);
        
        $tracking = [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'estimated_ready_time' => $order->estimated_ready_time,
            'items' => $order->orderItems->map(function ($item) {
                return [
                    'dish_name' => $item->dish->name,
                    'quantity' => $item->quantity,
                    'status' => $item->status,
                    'special_instructions' => $item->special_instructions,
                ];
            }),
        ];
        
        return response()->json([
            'tracking' => $tracking
        ]);
    }

    /**
     * Add a dish to user's favorites.
     */
    public function addToFavorites(Request $request)
    {
        $request->validate([
            'dish_id' => ['required', 'exists:dishes,id']
        ]);

        $user = auth()->user();
        $dishId = $request->dish_id;

        // Check if already favorited
        if ($user->favoriteDishes()->where('dish_id', $dishId)->exists()) {
            return response()->json([
                'message' => 'Dish is already in your favorites.'
            ], 400);
        }

        $user->favoriteDishes()->attach($dishId);

        return response()->json([
            'message' => 'Dish added to favorites successfully.'
        ], 201);
    }

    /**
     * Remove a dish from user's favorites.
     */
    public function removeFromFavorites($dishId)
    {
        $user = auth()->user();

        // Check if dish exists
        if (!Dish::find($dishId)) {
            return response()->json([
                'message' => 'Dish not found.'
            ], 404);
        }

        // Check if dish is in favorites
        if (!$user->favoriteDishes()->where('dish_id', $dishId)->exists()) {
            return response()->json([
                'message' => 'Dish is not in your favorites.'
            ], 400);
        }

        $user->favoriteDishes()->detach($dishId);

        return response()->json([
            'message' => 'Dish removed from favorites successfully.'
        ]);
    }

    /**
     * Get user's favorite dishes.
     */
    public function getFavorites(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->favoriteDishes()->with('category')->available();
        
        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $favorites = $query->ordered()->get();
        
        return response()->json([
            'favorites' => $favorites
        ]);
    }

    /**
     * Toggle dish favorite status.
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'dish_id' => ['required', 'exists:dishes,id']
        ]);

        $user = auth()->user();
        $dishId = $request->dish_id;

        $isFavorited = $user->favoriteDishes()->where('dish_id', $dishId)->exists();

        if ($isFavorited) {
            $user->favoriteDishes()->detach($dishId);
            $message = 'Dish removed from favorites successfully.';
            $action = 'removed';
        } else {
            $user->favoriteDishes()->attach($dishId);
            $message = 'Dish added to favorites successfully.';
            $action = 'added';
        }

        return response()->json([
            'message' => $message,
            'action' => $action,
            'is_favorited' => !$isFavorited
        ]);
    }
}
