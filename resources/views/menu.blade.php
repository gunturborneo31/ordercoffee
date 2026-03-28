@extends('layouts.app')

@section('title', 'Menu — Kafe Kita')

@section('content')
<div class="min-h-screen bg-stone-50" x-data="menuApp()" x-init="init()">

    {{-- Header --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-stone-100 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-stone-900">Kafe Kita</h1>
                <p class="text-xs text-stone-400" x-text="customer ? `Halo, ${customer.name}!` : 'Menu kami'"></p>
            </div>
            <div class="flex items-center gap-3">
                <a href="/orders/history" class="p-2 bg-stone-100 rounded-xl hover:bg-stone-200 transition text-stone-600" title="Riwayat Pesanan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>
                <button @click="showCart = true" class="relative p-2 bg-amber-50 rounded-xl hover:bg-amber-100 transition">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-4H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="cartCount > 0" x-text="cartCount"
                        class="absolute -top-1 -right-1 w-5 h-5 bg-amber-500 text-white text-xs font-bold rounded-full flex items-center justify-center" x-cloak></span>
                </button>
                <button @click="logout" class="p-2 text-stone-400 hover:text-stone-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Category tabs --}}
        <div class="max-w-2xl mx-auto px-4 pb-3 flex gap-2 overflow-x-auto scrollbar-hide">
            <button @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'"
                class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition">
                Semua
            </button>
            <button @click="activeCategory = 'coffee'"
                :class="activeCategory === 'coffee' ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'"
                class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition">
                ☕ Coffee
            </button>
            <button @click="activeCategory = 'non-coffee'"
                :class="activeCategory === 'non-coffee' ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'"
                class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition">
                🍵 Non-Coffee
            </button>
            <button @click="activeCategory = 'food'"
                :class="activeCategory === 'food' ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'"
                class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition">
                🥐 Makanan
            </button>
        </div>
    </header>

    {{-- Menu List --}}
    <main class="max-w-2xl mx-auto px-4 py-4 pb-32">
        <template x-if="loading">
            <div class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-4 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </template>

        <div x-show="!loading" class="grid gap-3 items-start" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
            <template x-for="menu in filteredMenus" :key="menu.id">
                 <div class="h-full bg-white rounded-2xl p-3 shadow-sm border border-stone-100 transition hover:shadow-md"
                     :class="!menu.is_available && 'opacity-60'">
                    <div class="relative">
                        <div class="aspect-square rounded-xl overflow-hidden bg-stone-100">
                            <template x-if="menu.image_url">
                                <img :src="menu.image_url" :alt="menu.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!menu.image_url">
                                <div class="w-full h-full flex items-center justify-center text-4xl" :class="getCategoryBg(menu.category)">
                                    <span x-text="getCategoryEmoji(menu.category)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="pt-6 text-center">
                        <h3 class="font-semibold text-stone-800 text-sm leading-tight line-clamp-2 min-h-[2.5rem]" x-text="menu.name"></h3>
                        <p class="text-[11px] text-stone-400 mt-0.5" x-text="menu.category.replace('-', ' ')"></p>
                        <p class="text-sm font-bold text-amber-600 mt-1" x-text="formatPrice(menu.price)"></p>
                    </div>

                    <div class="mt-3 flex items-center justify-center">
                        <template x-if="!menu.is_available">
                            <span class="text-xs px-2.5 py-1 rounded-full bg-stone-100 text-stone-500 font-medium">Habis</span>
                        </template>
                        <template x-if="menu.is_available">
                            <div class="flex items-center gap-1">
                                <button x-show="getCartQty(menu.id) > 0" x-cloak
                                    @click="decreaseCart(menu.id)"
                                    class="w-7 h-7 bg-stone-100 hover:bg-stone-200 rounded-lg flex items-center justify-center transition text-stone-600 font-bold">-</button>
                                <span x-show="getCartQty(menu.id) > 0" x-cloak
                                    class="w-6 text-center text-sm font-bold text-stone-800"
                                    x-text="getCartQty(menu.id)"></span>
                                <button @click="addToCart(menu)"
                                    class="w-8 h-8 bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-white rounded-lg flex items-center justify-center transition shadow">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="filteredMenus.length === 0 && !loading">
                <p class="col-span-2 text-center text-stone-400 py-12">Tidak ada menu di kategori ini.</p>
            </template>
        </div>
    </main>

    {{-- Sticky cart bar --}}
    <div x-show="cartCount > 0" x-cloak
        class="fixed bottom-0 left-0 right-0 z-20 bg-white border-t border-stone-100 px-4 py-4 shadow-lg">
        <div class="max-w-2xl mx-auto">
            <button @click="showCart = true"
                class="w-full py-4 bg-stone-900 hover:bg-stone-800 text-white font-semibold rounded-2xl flex items-center justify-between px-6 transition shadow-lg">
                <span class="bg-white/20 px-2.5 py-1 rounded-lg text-sm font-bold" x-text="cartCount + ' item'"></span>
                <span>Lihat Pesanan</span>
                <span x-text="formatPrice(cartTotal)"></span>
            </button>
        </div>
    </div>

    {{-- Cart Drawer --}}
    <div x-show="showCart" x-cloak class="fixed inset-0 z-40 flex" @keydown.escape.window="showCart = false">
        <div class="absolute inset-0 bg-black/50" @click="showCart = false"></div>
        <div class="relative ml-auto w-full max-w-md bg-white h-full flex flex-col shadow-2xl">
            {{-- Cart header --}}
            <div class="flex items-center justify-between p-5 border-b border-stone-100">
                <h2 class="text-lg font-bold text-stone-900">Pesanan Kamu</h2>
                <button @click="showCart = false" class="p-2 hover:bg-stone-100 rounded-xl transition text-stone-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Cart items --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                <template x-for="item in cart" :key="item.menu.id">
                    <div class="bg-stone-50 rounded-2xl p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                                 :class="getCategoryBg(item.menu.category)">
                                <span x-text="getCategoryEmoji(item.menu.category)"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="font-semibold text-stone-800 text-sm" x-text="item.menu.name"></h4>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <button @click="decreaseCart(item.menu.id)" class="w-6 h-6 bg-white border border-stone-200 rounded-lg flex items-center justify-center text-stone-600 hover:bg-stone-100 transition text-xs font-bold">−</button>
                                        <span class="w-5 text-center text-sm font-bold" x-text="item.quantity"></span>
                                        <button @click="addToCart(item.menu)" class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-white hover:bg-amber-400 transition text-xs font-bold">+</button>
                                    </div>
                                </div>
                                <p class="text-xs text-amber-600 font-medium mt-0.5" x-text="formatPrice(item.menu.price * item.quantity)"></p>
                                {{-- Note input --}}
                                <input type="text" x-model="item.note" placeholder="Catatan (opsional)..."
                                    class="mt-2 w-full text-xs px-3 py-2 bg-white border border-stone-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-400 text-stone-600 placeholder-stone-300">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Cart footer --}}
            <div class="p-5 border-t border-stone-100 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-1.5">Diantar Kemana?</label>
                    <input type="text" x-model="deliveryLocation" placeholder="Contoh: Meja 3, Kantor lantai 2..."
                        class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm text-stone-800 placeholder-stone-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-stone-600 font-medium">Total</span>
                    <span class="text-xl font-bold text-stone-900" x-text="formatPrice(cartTotal)"></span>
                </div>
                <button @click="placeOrder" :disabled="orderLoading || !deliveryLocation.trim()"
                    class="w-full py-4 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-300 text-white font-semibold rounded-2xl transition flex items-center justify-center gap-2 shadow">
                    <svg x-show="orderLoading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="orderLoading ? 'Mengirim...' : 'Kirim Pesanan'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function menuApp() {
    return {
        customer: null,
        menus: [],
        cart: [],
        activeCategory: 'all',
        showCart: false,
        loading: true,
        orderLoading: false,
        deliveryLocation: '',

        get filteredMenus() {
            if (this.activeCategory === 'all') return this.menus;
            return this.menus.filter(m => m.category === this.activeCategory);
        },

        get cartCount() {
            return this.cart.reduce((sum, i) => sum + i.quantity, 0);
        },

        get cartTotal() {
            return this.cart.reduce((sum, i) => sum + i.menu.price * i.quantity, 0);
        },

        async init() {
            // Load customer from cache
            const cached = localStorage.getItem('kafe_customer');
            if (!cached) {
                window.location.href = '/';
                return;
            }
            this.customer = JSON.parse(cached);

            // Load cart from cache
            const cachedCart = localStorage.getItem('kafe_cart');
            if (cachedCart) {
                try { this.cart = JSON.parse(cachedCart); } catch(e) {}
            }

            // Load menus
            await this.loadMenus();
        },

        async loadMenus() {
            this.loading = true;
            try {
                const res = await fetch('/api/menus');
                this.menus = await res.json();
            } finally {
                this.loading = false;
            }
        },

        getCartItem(menuId) {
            return this.cart.find(i => i.menu.id === menuId);
        },

        getCartQty(menuId) {
            const item = this.getCartItem(menuId);
            return item ? item.quantity : 0;
        },

        addToCart(menu) {
            const existing = this.getCartItem(menu.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({ menu, quantity: 1, note: '' });
            }
            this.saveCart();
        },

        decreaseCart(menuId) {
            const idx = this.cart.findIndex(i => i.menu.id === menuId);
            if (idx === -1) return;
            if (this.cart[idx].quantity <= 1) {
                this.cart.splice(idx, 1);
            } else {
                this.cart[idx].quantity--;
            }
            this.saveCart();
        },

        saveCart() {
            localStorage.setItem('kafe_cart', JSON.stringify(this.cart));
        },

        async placeOrder() {
            if (!this.customer || this.cart.length === 0) return;
            this.orderLoading = true;
            try {
                const items = this.cart.map(i => ({
                    menu_id: i.menu.id,
                    quantity: i.quantity,
                    note: i.note || null,
                }));

                const res = await fetch('/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        customer_id: this.customer.id,
                        delivery_location: this.deliveryLocation.trim(),
                        items,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    localStorage.removeItem('kafe_cart');
                    this.cart = [];
                    this.deliveryLocation = '';
                    this.showCart = false;
                    window.location.href = `/orders/${data.order.id}`;
                } else {
                    showToast('Gagal mengirim pesanan.', 'error');
                }
            } catch(e) {
                showToast('Koneksi bermasalah.', 'error');
            } finally {
                this.orderLoading = false;
            }
        },

        formatPrice(val) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
        },

        getCategoryBg(cat) {
            const map = { coffee: 'bg-amber-50', 'non-coffee': 'bg-emerald-50', food: 'bg-orange-50' };
            return map[cat] || 'bg-stone-100';
        },

        getCategoryEmoji(cat) {
            const map = { coffee: '☕', 'non-coffee': '🍵', food: '🥐' };
            return map[cat] || '🍽️';
        },

        logout() {
            if (confirm('Keluar dari akun?')) {
                localStorage.removeItem('kafe_customer');
                localStorage.removeItem('kafe_cart');
                window.location.href = '/';
            }
        }
    };
}
</script>
@endpush
