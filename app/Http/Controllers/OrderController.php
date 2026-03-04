<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\QrisService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request, QrisService $qris)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string|max:500',
        ]);

        $order = Order::create([
            'customer_id' => $validated['customer_id'],
            'status' => 'pending',
            'total' => 0,
        ]);

        $total = 0;
        foreach ($validated['items'] as $item) {
            $menu = Menu::findOrFail($item['menu_id']);
            $subtotal = $menu->price * $item['quantity'];
            $total += $subtotal;

            OrderItem::create([
                'order_id' => $order->id,
                'menu_id' => $item['menu_id'],
                'quantity' => $item['quantity'],
                'price' => $menu->price,
                'note' => $item['note'] ?? null,
            ]);
        }

        $order->update(['total' => $total]);
        $order->load(['items.menu', 'customer']);

        return response()->json(['success' => true, 'order' => $order]);
    }

    public function show(Order $order)
    {
        $order->load(['items.menu', 'customer', 'messages']);

        return view('order', compact('order'));
    }

    public function apiShow(Order $order)
    {
        $order->load(['items.menu', 'customer', 'messages']);

        return response()->json($order);
    }

    public function updateItems(Request $request, Order $order)
    {
        if (!in_array($order->status, ['pending', 'reviewing'])) {
            return response()->json(['error' => 'Pesanan tidak dapat diubah pada status ini.'], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string|max:500',
        ]);

        $order->items()->delete();
        $total = 0;
        foreach ($validated['items'] as $item) {
            $menu = Menu::findOrFail($item['menu_id']);
            $subtotal = $menu->price * $item['quantity'];
            $total += $subtotal;
            OrderItem::create([
                'order_id' => $order->id,
                'menu_id' => $item['menu_id'],
                'quantity' => $item['quantity'],
                'price' => $menu->price,
                'note' => $item['note'] ?? null,
            ]);
        }
        $order->update(['total' => $total]);
        $order->load(['items.menu', 'customer']);

        return response()->json(['success' => true, 'order' => $order]);
    }

    public function updateStatus(Request $request, Order $order, QrisService $qris)
    {
        $validated = $request->validate([
            'status' => 'required|in:reviewing,confirmed,ready,completed,cancelled',
            'qris_static' => 'nullable|string',
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'confirmed') {
            $staticQris = $validated['qris_static'] ?? config('app.qris_static', '');
            if ($staticQris) {
                $data['qris_string'] = $qris->generateWithAmount($staticQris, (int) $order->total);
            }
        }

        $order->update($data);
        $order->load(['items.menu', 'customer']);

        return response()->json(['success' => true, 'order' => $order]);
    }
}
