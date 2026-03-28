@extends('layouts.app')

@section('title', 'Kategori POS - Barista')

@section('content')
@php
    $categoryCount = $categories->count();
    $selectedCategoryId = $selectedCategory?->id;
@endphp

<div class="min-h-screen bg-stone-900">
    <header class="border-b border-stone-700 bg-stone-900/95 backdrop-blur sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 py-4 md:px-6 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] uppercase tracking-[0.2em] text-stone-400">Barista Panel</p>
                <h1 class="text-xl md:text-2xl font-semibold text-white">Category Management</h1>
            </div>
            <nav class="flex items-center gap-2 overflow-x-auto">
                <a href="{{ route('barista.dashboard') }}" class="px-3 py-2 text-sm rounded-lg border border-stone-600 text-stone-300 hover:bg-stone-800">Dashboard</a>
                <a href="{{ route('barista.menus') }}" class="px-3 py-2 text-sm rounded-lg border border-stone-600 text-stone-300 hover:bg-stone-800">Menu</a>
                <a href="{{ route('barista.categories') }}" class="px-3 py-2 text-sm rounded-lg bg-amber-500 text-white">Kategori</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6 md:px-6 grid grid-cols-1 lg:grid-cols-5 gap-5">
        <section class="lg:col-span-2">
            <div class="bg-stone-800 border border-stone-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-stone-700 flex items-center justify-between">
                    <p class="text-sm font-semibold text-stone-100">Daftar Kategori</p>
                    <span class="text-xs text-stone-400">{{ $categoryCount }} item</span>
                </div>
                <div class="p-3 border-b border-stone-700">
                    <a href="{{ route('barista.categories', ['mode' => 'create']) }}" class="w-full inline-flex justify-center px-3 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-400">
                        + Tambah Kategori
                    </a>
                </div>
                <div class="max-h-[68vh] overflow-y-auto p-3 space-y-2">
                    @forelse ($categories as $category)
                        <a href="{{ route('barista.categories', ['mode' => 'edit', 'category' => $category->id]) }}"
                            class="block rounded-xl border px-3 py-2.5 transition {{ $selectedCategoryId === $category->id ? 'border-amber-500 bg-stone-700 text-white' : 'border-stone-700 bg-stone-800 text-stone-200 hover:border-stone-500 hover:bg-stone-700/70' }}">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium text-sm">{{ $category->name }}</p>
                                <p class="text-xs {{ $selectedCategoryId === $category->id ? 'text-stone-300' : 'text-stone-400' }}">{{ $category->menus_count }} menu</p>
                            </div>
                            <p class="mt-1 text-xs {{ $selectedCategoryId === $category->id ? 'text-stone-300' : 'text-stone-400' }}">{{ $category->slug }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-stone-400 px-2 py-4">Belum ada kategori.</p>
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

            @if ($mode === 'edit' && $selectedCategory)
                <div class="bg-stone-800 border border-stone-700 rounded-2xl shadow-sm p-5">
                    <div class="mb-4">
                        <p class="text-xs uppercase tracking-wider text-stone-400">Edit Kategori</p>
                        <h2 class="text-lg font-semibold text-white">{{ $selectedCategory->name }}</h2>
                    </div>
                    <form action="{{ route('barista.categories.update', $selectedCategory) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="text-xs text-stone-400">Nama Kategori</label>
                            <input type="text" name="name" value="{{ old('name', $selectedCategory->name) }}" required class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div class="text-xs text-stone-400">Slug saat ini: {{ $selectedCategory->slug }}</div>
                        <div class="flex flex-col sm:flex-row gap-2 pt-2">
                            <button type="submit" class="px-4 py-2.5 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-400">Simpan Perubahan</button>
                            <a href="{{ route('barista.categories', ['mode' => 'create']) }}" class="px-4 py-2.5 rounded-lg border border-stone-600 text-stone-300 text-sm text-center hover:bg-stone-700">Form Tambah</a>
                        </div>
                    </form>
                    <form action="{{ route('barista.categories.destroy', $selectedCategory) }}" method="POST" class="pt-4 mt-4 border-t border-stone-700" onsubmit="return confirm('Hapus kategori ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2.5 rounded-lg border border-red-700 text-red-300 text-sm font-medium hover:bg-red-900/30 {{ $selectedCategory->menus_count > 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $selectedCategory->menus_count > 0 ? 'disabled' : '' }}>
                            Hapus Kategori
                        </button>
                        @if ($selectedCategory->menus_count > 0)
                            <p class="text-xs text-stone-400 mt-2">Kategori tidak bisa dihapus karena masih dipakai menu.</p>
                        @endif
                    </form>
                </div>
            @else
                <div class="bg-stone-800 border border-stone-700 rounded-2xl shadow-sm p-5">
                    <div class="mb-4">
                        <p class="text-xs uppercase tracking-wider text-stone-400">Tambah Kategori</p>
                        <h2 class="text-lg font-semibold text-white">Kategori Baru</h2>
                    </div>
                    <form action="{{ route('barista.categories.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-xs text-stone-400">Nama Kategori</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full px-3 py-2.5 rounded-lg border border-stone-600 bg-stone-700 text-white focus:outline-none focus:ring-2 focus:ring-amber-400" placeholder="Contoh: Signature Series">
                        </div>
                        <button type="submit" class="px-4 py-2.5 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-400">Simpan Kategori</button>
                    </form>
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
