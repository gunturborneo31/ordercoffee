<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Order;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'sender_type' => 'required|in:customer,barista',
            'sender_name' => 'required|string|max:100',
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'order_id' => $order->id,
            'sender_type' => $validated['sender_type'],
            'sender_name' => $validated['sender_name'],
            'content' => $validated['content'],
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function index(Order $order)
    {
        $messages = $order->messages()->orderBy('created_at')->get();

        return response()->json($messages);
    }

    public function markRead(Order $order)
    {
        $order->messages()->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
