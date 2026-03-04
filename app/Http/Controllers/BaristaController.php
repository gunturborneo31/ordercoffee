<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class BaristaController extends Controller
{
    public function dashboard()
    {
        $orders = Order::with(['customer', 'items.menu'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderByDesc('created_at')
            ->get();

        return view('barista.dashboard', compact('orders'));
    }

    public function order(Order $order)
    {
        $order->load(['items.menu', 'customer', 'messages']);

        return view('barista.order', compact('order'));
    }

    public function apiOrders()
    {
        $orders = Order::with(['customer', 'items.menu'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($orders);
    }

    public function apiOrder(Order $order)
    {
        $order->load(['items.menu', 'customer', 'messages']);

        return response()->json($order);
    }
}
