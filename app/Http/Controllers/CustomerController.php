<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function showRegistration()
    {
        return view('welcome');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
        ]);

        $customer = Customer::firstOrCreate(
            ['phone' => $validated['phone']],
            ['name' => $validated['name']]
        );

        if ($customer->wasRecentlyCreated === false) {
            $customer->update(['name' => $validated['name']]);
        }

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    public function getByPhone(Request $request)
    {
        $phone = $request->query('phone');
        $customer = Customer::where('phone', $phone)->first();

        if (!$customer) {
            return response()->json(['found' => false]);
        }

        return response()->json(['found' => true, 'customer' => $customer]);
    }
}
