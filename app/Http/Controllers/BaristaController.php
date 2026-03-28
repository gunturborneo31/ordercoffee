<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

    public function menus(Request $request)
    {
        $this->ensureDefaultCategories();

        $menus = Menu::orderBy('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $mode = $request->query('mode', 'create');
        $selectedMenu = null;

        if ($request->filled('menu')) {
            $selectedMenu = $menus->firstWhere('id', (int) $request->query('menu'));
        }

        if ($mode === 'edit' && ! $selectedMenu && $menus->isNotEmpty()) {
            $selectedMenu = $menus->first();
        }

        return view('barista.menus', compact('menus', 'categories', 'mode', 'selectedMenu'));
    }

    public function categories(Request $request)
    {
        $this->ensureDefaultCategories();

        $categories = Category::withCount('menus')->orderBy('name')->get();
        $mode = $request->query('mode', 'create');
        $selectedCategory = null;

        if ($request->filled('category')) {
            $selectedCategory = $categories->firstWhere('id', (int) $request->query('category'));
        }

        if ($mode === 'edit' && ! $selectedCategory && $categories->isNotEmpty()) {
            $selectedCategory = $categories->first();
        }

        return view('barista.categories', compact('categories', 'mode', 'selectedCategory'));
    }

    public function storeMenu(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'category' => ['required', 'string', Rule::exists('categories', 'slug')],
            'image_url' => 'nullable|string|max:2048',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imageUrl = $validated['image_url'] ?? null;
        if ($request->hasFile('image')) {
            $imageUrl = $this->storeMenuImage($request);
        }

        Menu::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'category' => $validated['category'],
            'image_url' => $imageUrl ?: $this->defaultImageForCategory($validated['category']),
            'description' => null,
            'is_available' => true,
        ]);

        return redirect()->route('barista.menus')->with('success', 'Menu baru berhasil ditambahkan.');
    }

    public function updateMenu(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'category' => ['required', 'string', Rule::exists('categories', 'slug')],
            'image_url' => 'nullable|string|max:2048',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imageUrl = $validated['image_url'] ?? null;
        if ($request->hasFile('image')) {
            $this->deleteStoredMenuImage($menu->image_url);
            $imageUrl = $this->storeMenuImage($request);
        }

        $validated['image_url'] = $imageUrl ?: $this->defaultImageForCategory($validated['category']);

        $menu->update($validated);

        return redirect()->route('barista.menus')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroyMenu(Menu $menu)
    {
        $this->deleteStoredMenuImage($menu->image_url);
        $menu->delete();

        return redirect()->route('barista.menus')->with('success', 'Menu berhasil dihapus.');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name']),
        ]);

        return redirect()->route('barista.categories')->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        $newSlug = $this->generateUniqueSlug($validated['name'], $category->id);

        if ($newSlug !== $category->slug) {
            Menu::where('category', $category->slug)->update(['category' => $newSlug]);
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $newSlug,
        ]);

        return redirect()->route('barista.categories')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory(Category $category)
    {
        $menusUsingCategory = Menu::where('category', $category->slug)->count();

        if ($menusUsingCategory > 0) {
            return redirect()->route('barista.categories')->with('error', 'Kategori tidak bisa dihapus karena masih dipakai menu.');
        }

        $category->delete();

        return redirect()->route('barista.categories')->with('success', 'Kategori berhasil dihapus.');
    }

    private function ensureDefaultCategories(): void
    {
        $defaults = [
            ['name' => 'Coffee', 'slug' => 'coffee'],
            ['name' => 'Non Coffee', 'slug' => 'non-coffee'],
            ['name' => 'Food', 'slug' => 'food'],
        ];

        foreach ($defaults as $default) {
            Category::firstOrCreate(['slug' => $default['slug']], ['name' => $default['name']]);
        }
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $normalizedBase = $baseSlug !== '' ? $baseSlug : 'kategori';
        $slug = $normalizedBase;
        $counter = 1;

        while (Category::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $counter++;
            $slug = $normalizedBase . '-' . $counter;
        }

        return $slug;
    }

    private function defaultImageForCategory(string $category): string
    {
        $defaults = [
            'coffee' => '/images/menu/default-coffee.svg',
            'non-coffee' => '/images/menu/default-non-coffee.svg',
            'food' => '/images/menu/default-food.svg',
        ];

        return $defaults[$category] ?? '/images/menu/default-food.svg';
    }

    private function storeMenuImage(Request $request): string
    {
        $path = $request->file('image')->store('menu-images', 'public');

        return Storage::url($path);
    }

    private function deleteStoredMenuImage(?string $imageUrl): void
    {
        if (! $imageUrl || ! str_starts_with($imageUrl, '/storage/menu-images/')) {
            return;
        }

        $relativePath = str_replace('/storage/', '', $imageUrl);
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
