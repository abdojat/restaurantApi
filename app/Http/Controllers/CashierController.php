<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class CashierController extends Controller
{
    /**
     * Get all orders for cashier management.
     */
    public function getOrders(Request $request)
    {
        $query = Order::with(['user', 'table', 'orderItems.dish']);
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'orders' => $orders
        ]);
    }

    /**
     * Get a specific order with details.
     */
    public function getOrder($id)
    {
        $order = Order::with(['user', 'table', 'orderItems.dish'])->findOrFail($id);
        
        return response()->json([
            'order' => $order
        ]);
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $data = $request->validate([
            'status' => ['required', 'in:received,preparing,with_courier,out_for_delivery,delivered,delivery_failed'],
            'notes' => ['nullable', 'string'],
        ]);

        // Check if cashier can update this order
        if (!$order->canBeUpdatedByCashier()) {
            return response()->json([
                'message' => 'Cannot update order with current status.'
            ], 400);
        }

        $previousStatus = $order->status;
        $order->update($data);

        // If delivery failed, increment user's failed deliveries and ban if necessary
        if ($order->status === 'delivery_failed' && $previousStatus !== 'delivery_failed') {
            $user = $order->user;
            $user->failed_deliveries_count = ($user->failed_deliveries_count ?? 0) + 1;
            if ($user->failed_deliveries_count >= 2 && !$user->is_banned) {
                $user->is_banned = true;
                $user->banned_at = now();
            }
            $user->save();
        }

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => $order->load(['user', 'table', 'orderItems.dish'])
        ]);
    }

    /**
     * Update order item status.
     */
    public function updateOrderItemStatus(Request $request, $orderId, $itemId)
    {
        $orderItem = OrderItem::where('order_id', $orderId)
            ->where('id', $itemId)
            ->firstOrFail();
        
        $data = $request->validate([
            'status' => ['required', 'in:pending,preparing,ready,served'],
            'notes' => ['nullable', 'string'],
        ]);

        // Check if cashier can update this item
        if (!$orderItem->canBeUpdatedByCashier()) {
            return response()->json([
                'message' => 'Cannot update item with current status.'
            ], 400);
        }

        $orderItem->update($data);

        // Check if all items are served and update order status accordingly
        $order = $orderItem->order;
        if ($order->orderItems()->where('status', '!=', 'served')->doesntExist()) {
            if ($order->type === 'delivery') {
                $order->update(['status' => 'with_courier']);
            } else {
                $order->update(['status' => 'delivered']);
            }
        }

        return response()->json([
            'message' => 'Order item status updated successfully.',
            'order_item' => $orderItem->load('dish'),
            'order' => $order->load(['user', 'table', 'orderItems.dish'])
        ]);
    }

    /**
     * Get orders that need attention (pending/processing).
     */
    public function getOrdersNeedingAttention()
    {
        $orders = Order::with(['user', 'table', 'orderItems.dish'])
            ->whereIn('status', ['received', 'preparing', 'with_courier', 'out_for_delivery'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        return response()->json([
            'orders' => $orders
        ]);
    }

    /**
     * Get today's orders summary.
     */
    public function getTodayOrdersSummary()
    {
        $today = today();
        
        $summary = [
            'total_orders' => Order::whereDate('created_at', $today)->count(),
            'received_orders' => Order::whereDate('created_at', $today)->received()->count(),
            'preparing_orders' => Order::whereDate('created_at', $today)->preparing()->count(),
            'with_courier_orders' => Order::whereDate('created_at', $today)->withCourier()->count(),
            'out_for_delivery_orders' => Order::whereDate('created_at', $today)->outForDelivery()->count(),
            'delivered_orders' => Order::whereDate('created_at', $today)->delivered()->count(),
            'delivery_failed_orders' => Order::whereDate('created_at', $today)->deliveryFailed()->count(),
            'total_revenue' => Order::whereDate('created_at', $today)
                ->where('status', 'delivered')
                ->sum('total_amount'),
        ];
        
        return response()->json([
            'summary' => $summary
        ]);
    }

    /**
     * Mark order as delivered.
     */
    public function markOrderDelivered($id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status !== 'out_for_delivery') {
            return response()->json([
                'message' => 'Order must be out for delivery before marking as delivered.'
            ], 400);
        }

        $order->update(['status' => 'delivered']);

        return response()->json([
            'message' => 'Order marked as delivered successfully.',
            'order' => $order->load(['user', 'table', 'orderItems.dish'])
        ]);
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Disallow cancelling at late stages or if already cancelled
        if (in_array($order->status, ['delivered', 'delivery_failed', 'cancelled', 'with_courier', 'out_for_delivery'])) {
            return response()->json([
                'message' => 'Order cannot be cancelled at this stage.'
            ], 400);
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $order->status = 'cancelled';

        if (!empty($data['notes'])) {
            $existingNotes = $order->notes ? $order->notes . "\n" : '';
            $order->notes = $existingNotes . 'Cancellation Notes: ' . $data['notes'];
        }

        $order->save();

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'order' => $order->load(['user', 'table', 'orderItems.dish'])
        ]);
    }

    /**
     * Get orders by table.
     */
    public function getOrdersByTable($tableId)
    {
        $orders = Order::with(['user', 'orderItems.dish'])
            ->where('table_id', $tableId)
            ->whereIn('status', ['received', 'preparing'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'orders' => $orders
        ]);
    }

    /**
     * Get orders by user.
     */
    public function getOrdersByUser($userId)
    {
        $orders = Order::with(['table', 'orderItems.dish'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return response()->json([
            'orders' => $orders
        ]);
    }
}
