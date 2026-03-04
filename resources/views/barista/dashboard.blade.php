@extends('layouts.app')

@section('title', 'Dashboard Barista — Kafe Kita')

@section('content')
<div class="min-h-screen bg-stone-900" x-data="baristaApp()" x-init="init()">

    {{-- Header --}}
    <header class="sticky top-0 z-30 bg-stone-900 border-b border-stone-700">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-white">☕ Dashboard Barista</h1>
                <p class="text-xs text-stone-400 mt-0.5" x-text="`${activeOrders.length} pesanan aktif`"></p>
            </div>
            <div class="flex items-center gap-3">
                <div x-show="newOrderCount > 0" x-cloak
                    class="flex items-center gap-1.5 bg-red-500 text-white px-3 py-1.5 rounded-full text-xs font-bold animate-pulse">
                    <span>🔔</span>
                    <span x-text="`${newOrderCount} baru`"></span>
                </div>
                <button @click="loadOrders" class="p-2 text-stone-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
                <a href="/" class="p-2 text-stone-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Status filter --}}
        <div class="max-w-3xl mx-auto px-4 pb-3 flex gap-2 overflow-x-auto scrollbar-hide">
            <template x-for="filter in filters" :key="filter.value">
                <button @click="activeFilter = filter.value"
                    :class="activeFilter === filter.value ? 'bg-amber-500 text-white' : 'bg-stone-800 text-stone-400 hover:bg-stone-700'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition">
                    <span x-text="filter.label"></span>
                </button>
            </template>
        </div>
    </header>

    {{-- Orders --}}
    <main class="max-w-3xl mx-auto px-4 py-4 space-y-3">
        <template x-if="loading">
            <div class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-4 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </template>

        <template x-for="order in filteredOrders" :key="order.id">
            <div class="bg-stone-800 rounded-2xl overflow-hidden border border-stone-700 hover:border-stone-600 transition">
                {{-- Order header --}}
                <div class="px-5 py-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-500/20 rounded-xl flex items-center justify-center">
                            <span class="text-amber-400 font-bold text-sm" x-text="`#${order.id}`"></span>
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm" x-text="order.customer?.name || 'Customer'"></p>
                            <p class="text-xs text-stone-400" x-text="order.customer?.phone"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium px-2 py-1 rounded-full" :class="statusClass(order.status)" x-text="statusLabel(order.status)"></span>
                        <p class="text-xs text-stone-400 mt-1" x-text="formatTime(order.created_at)"></p>
                    </div>
                </div>

                {{-- Order items --}}
                <div class="px-5 pb-3 space-y-1">
                    <template x-for="item in order.items" :key="item.id">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-stone-300" x-text="`${item.quantity}× ${item.menu?.name}`"></span>
                            <span class="text-stone-400" x-text="formatPrice(item.price * item.quantity)"></span>
                        </div>
                    </template>
                    <div class="border-t border-stone-700 mt-2 pt-2 flex items-center justify-between">
                        <span class="text-sm font-semibold text-stone-300">Total</span>
                        <span class="text-sm font-bold text-amber-400" x-text="formatPrice(order.total)"></span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="px-5 pb-4 flex gap-2 flex-wrap">
                    <a :href="`/barista/orders/${order.id}`"
                        class="flex-1 text-center py-2.5 bg-stone-700 hover:bg-stone-600 text-stone-200 rounded-xl text-sm font-medium transition">
                        Kelola &amp; Chat
                    </a>

                    <template x-if="order.status === 'pending'">
                        <button @click="updateStatus(order.id, 'reviewing')"
                            class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 text-white rounded-xl text-sm font-medium transition">
                            Tinjau
                        </button>
                    </template>

                    <template x-if="order.status === 'ready'">
                        <button @click="updateStatus(order.id, 'completed')"
                            class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-medium transition">
                            ✓ Selesai
                        </button>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="!loading && filteredOrders.length === 0">
            <div class="text-center py-20 text-stone-500">
                <div class="text-5xl mb-4">☕</div>
                <p class="font-medium">Tidak ada pesanan</p>
                <p class="text-sm mt-1">Pesanan baru akan muncul di sini</p>
            </div>
        </template>
    </main>
</div>
@endsection

@push('scripts')
<script>
function baristaApp() {
    return {
        orders: [],
        activeFilter: 'all',
        loading: true,
        newOrderCount: 0,
        lastOrderCount: 0,
        filters: [
            { value: 'all', label: 'Semua' },
            { value: 'pending', label: '⏳ Baru' },
            { value: 'reviewing', label: '👀 Ditinjau' },
            { value: 'confirmed', label: '💳 Konfirmasi' },
            { value: 'ready', label: '✅ Siap' },
        ],

        get activeOrders() { return this.orders; },

        get filteredOrders() {
            if (this.activeFilter === 'all') return this.orders;
            return this.orders.filter(o => o.status === this.activeFilter);
        },

        async init() {
            await this.loadOrders();
            this.lastOrderCount = this.orders.length;
            setInterval(() => this.loadOrders(), 5000);
        },

        async loadOrders() {
            try {
                const res = await fetch('/barista/api/orders');
                const orders = await res.json();

                const pendingNew = orders.filter(o => o.status === 'pending').length;
                const prevPending = this.orders.filter(o => o.status === 'pending').length;

                if (orders.length > this.lastOrderCount) {
                    const newCount = orders.length - this.lastOrderCount;
                    this.newOrderCount = pendingNew;
                    this.playNotifSound();
                    showToast(`${newCount} pesanan baru masuk!`, 'info');
                } else {
                    this.newOrderCount = pendingNew;
                }

                this.lastOrderCount = orders.length;
                this.orders = orders;
            } finally {
                this.loading = false;
            }
        },

        async updateStatus(orderId, status) {
            try {
                const res = await fetch(`/api/orders/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status }),
                });
                const data = await res.json();
                if (data.success) {
                    const idx = this.orders.findIndex(o => o.id === orderId);
                    if (idx !== -1) this.orders[idx] = data.order;
                    showToast(`Status diperbarui: ${this.statusLabel(status)}`, 'success');
                }
            } catch(e) {
                showToast('Gagal memperbarui status.', 'error');
            }
        },

        playNotifSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                [440, 550, 660].forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = freq;
                    const t = ctx.currentTime + i * 0.15;
                    gain.gain.setValueAtTime(0.3, t);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.3);
                    osc.start(t);
                    osc.stop(t + 0.3);
                });
            } catch(e) {}
        },

        statusLabel(status) {
            const labels = {
                pending: 'Baru', reviewing: 'Ditinjau', confirmed: 'Dikonfirmasi',
                ready: 'Siap', completed: 'Selesai', cancelled: 'Dibatalkan',
            };
            return labels[status] || status;
        },

        statusClass(status) {
            const classes = {
                pending: 'bg-blue-900/50 text-blue-300',
                reviewing: 'bg-amber-900/50 text-amber-300',
                confirmed: 'bg-purple-900/50 text-purple-300',
                ready: 'bg-emerald-900/50 text-emerald-300',
                completed: 'bg-stone-700 text-stone-400',
                cancelled: 'bg-red-900/50 text-red-300',
            };
            return classes[status] || 'bg-stone-700 text-stone-400';
        },

        formatPrice(val) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
        },

        formatTime(ts) {
            return new Date(ts).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
    };
}
</script>
@endpush
