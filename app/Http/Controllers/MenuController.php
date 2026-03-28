<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::orderBy('category')->orderBy('name')->get();
        $grouped = $menus->groupBy('category');

        return view('menu', compact('menus', 'grouped'));
    }

    public function apiIndex()
    {
        return response()->json(Menu::orderBy('category')->orderBy('name')->get());
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'is_available' => 'required|boolean',
        ]);
        $menu->update($validated);

        return response()->json(['success' => true, 'menu' => $menu]);
    }
}
