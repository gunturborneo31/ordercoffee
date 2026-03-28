@extends('layouts.app')

@section('title', 'Menu POS - Barista')

@section('content')
@php
    $menuCount = $menus->count();
    $selectedMenuId = $selectedMenu?->id;
@endphp

<div class="min-h-screen bg-stone-900">
    <header class="border-b border-stone-700 bg-stone-900/95 backdrop-blur sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 py-4 md:px-6 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] uppercase tracking-[0.2em] text-stone-400">Barista Panel</p>
                <h1 class="text-xl md:text-2xl font-semibold text-white">Menu Management</h1>
            </div>
            <nav class="flex items-center gap-2 overflow-x-auto">
                <a href="{{ route('barista.dashboard') }}" class="px-3 py-2 text-sm rounded-lg border border-stone-600 text-stone-300 hover:bg-stone-800">Dashboard</a>
                <a href="{{ route('barista.menus') }}" class="px-3 py-2 text-sm rounded-lg bg-amber-500 text-white">Menu</a>
                <a href="{{ route('barista.categories') }}" class="px-3 py-2 text-sm rounded-lg border border-stone-600 text-stone-300 hover:bg-stone-800">Kategori</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6 md:px-6 grid grid-cols-1 lg:grid-cols-5 gap-5">
        <section class="lg:col-span-2">
            <div class="bg-stone-800 border border-stone-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-stone-700 flex items-center justify-between">
                    <p class="text-sm font-semibold text-stone-100">Daftar Menu</p>
                    <span class="text-xs text-stone-400">{{ $menuCount }} item</span>
                </div>
                <div class="p-3 border-b border-stone-700">
                    <a href="{{ route('barista.menus', ['mode' => 'create']) }}" class="w-full inline-flex justify-center px-3 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-400">
                        + Tambah Menu
                    </a>
                </div>
                <div class="max-h-[68vh] overflow-y-auto p-3 space-y-2">
                    @forelse ($menus as $menu)
                        <a href="{{ route('barista.menus', ['mode' => 'edit', 'menu' => $menu->id]) }}"
                            class="block rounded-xl border px-3 py-2.5 transition {{ $selectedMenuId === $menu->id ? 'border-amber-500 bg-stone-700 text-white' : 'border-stone-700 bg-stone-800 text-stone-200 hover:border-stone-500 hover:bg-stone-700/70' }}">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium text-sm">{{ $menu->name }}</p>
                                <p class="text-xs {{ $selectedMenuId === $menu->id ? 'text-stone-300' : 'text-stone-400' }}">Rp{{ number_format((int) $menu->price, 0, ',', '.') }}</p>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-xs">
                                <span class="{{ $selectedMenuId === $menu->id ? 'text-stone-300' : 'text-stone-400' }}">{{ ucfirst(str_replace('-', ' ', $menu->category)) }}</span>
                                <span class="{{ $menu->is_available ? ($selectedMenuId === $menu->id ? 'text-emerald-300' : 'text-emerald-600') : ($selectedMenuId === $menu->id ? 'text-red-300' : 'text-red-500') }}">{{ $menu->is_available ? 'Tersedia' : 'Habis' }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-stone-400 px-2 py-4">Belum ada menu.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="lg:col-span-3 space-y-4">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-700 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-300">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-red-700 bg-red-900/30 px-4 py-3 text-sm text-red-300 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if ($mode === 'edit' && $selectedMenu)
                <div class="bg-stone-800 border border-stone-700 rounded-2xl shadow-sm p-5">
                    <div class="mb-4">
                        <p class="text-xs uppercase tracking-wider text-stone-400">Edit Menu</p>
                        <h2 class="text-lg font-semibold text-white">{{ $selectedMenu->name }}</h2>
                    </div>
                    <form action="{{ route('barista.menus.update', $selectedMenu) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="text-xs text-stone-400">Nama Menu</label>
                            <input type="text" name="name" value="{{ old('name', $selectedMenu->name) }}" required class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-stone-400">Harga</label>
                                <input type="number" name="price" value="{{ old('price', (int) $selectedMenu->price) }}" min="0" required class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                            </div>
                            <div>
                                <label class="text-xs text-stone-400">Kategori</label>
                                <select name="category" required class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}" {{ old('category', $selectedMenu->category) === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-stone-400">URL Gambar</label>
                            <input type="text" name="image_url" value="{{ old('image_url', $selectedMenu->image_url) }}" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400" placeholder="/images/menu/default-coffee.svg atau https://...">
                            <p class="mt-1 text-[11px] text-stone-500">Boleh dikosongkan jika upload file.</p>
                        </div>
                        <div>
                            <label class="text-xs text-stone-400">Upload Gambar</label>
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 w-full text-sm text-stone-300 file:mr-4 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-amber-400">
                            <p class="mt-1 text-[11px] text-stone-500">Format JPG, PNG, WEBP. Maksimal 2MB.</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 pt-2">
                            <button type="submit" class="px-4 py-2.5 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-400">Simpan Perubahan</button>
                            <a href="{{ route('barista.menus', ['mode' => 'create']) }}" class="px-4 py-2.5 rounded-lg border border-stone-600 text-stone-300 text-sm text-center hover:bg-stone-700">Form Tambah</a>
                        </div>
                    </form>
                    <form action="{{ route('barista.menus.destroy', $selectedMenu) }}" method="POST" class="pt-4 mt-4 border-t border-stone-700" onsubmit="return confirm('Hapus menu ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2.5 rounded-lg border border-red-700 text-red-300 text-sm font-medium hover:bg-red-900/30">
                            Hapus Menu
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-stone-800 border border-stone-700 rounded-2xl shadow-sm p-5">
                    <div class="mb-4">
                        <p class="text-xs uppercase tracking-wider text-stone-400">Tambah Menu</p>
                        <h2 class="text-lg font-semibold text-white">Menu Baru</h2>
                    </div>
                    <form action="{{ route('barista.menus.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-xs text-stone-400">Nama Menu</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400" placeholder="Contoh: Cold Espresso">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-stone-400">Harga</label>
                                <input type="number" name="price" value="{{ old('price') }}" min="0" required class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400" placeholder="28000">
                            </div>
                            <div>
                                <label class="text-xs text-stone-400">Kategori</label>
                                <select name="category" required class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>Pilih kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}" {{ old('category') === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-stone-400">URL Gambar</label>
                            <input type="text" name="image_url" value="{{ old('image_url') }}" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400" placeholder="/images/menu/default-coffee.svg atau https://...">
                            <p class="mt-1 text-[11px] text-stone-500">Boleh dikosongkan jika upload file.</p>
                        </div>
                        <div>
                            <label class="text-xs text-stone-400">Upload Gambar</label>
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 w-full text-sm text-stone-300 file:mr-4 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-amber-400">
                            <p class="mt-1 text-[11px] text-stone-500">Format JPG, PNG, WEBP. Maksimal 2MB.</p>
                        </div>
                        <button type="submit" class="px-4 py-2.5 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-400">Simpan Menu</button>
                    </form>
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
