@extends('layouts.app')

@section('title', 'Riwayat Pesanan — Kafe Kita')

@section('content')
<div class="min-h-screen bg-stone-50" x-data="historyApp()" x-init="init()">
    <header class="sticky top-0 z-30 bg-white border-b border-stone-100 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
            <a href="/menu" class="p-2 hover:bg-stone-100 rounded-xl transition text-stone-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-base font-bold text-stone-900">Riwayat Pesanan</h1>
                <p class="text-xs text-stone-400" x-text="customer ? customer.name : 'Pelanggan'"></p>
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-4 pb-8">
        <template x-if="loading">
            <div class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-4 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </template>

        <div x-show="!loading" class="space-y-3">
            <template x-if="orders.length === 0">
                <div class="bg-white border border-stone-100 rounded-2xl p-6 text-center">
                    <p class="text-stone-700 font-semibold">Belum ada riwayat pesanan.</p>
                    <p class="text-sm text-stone-400 mt-1">Pesanan yang kamu buat akan tampil di sini.</p>
                    <a href="/menu" class="mt-4 inline-block px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-white rounded-xl text-sm font-semibold transition">Pesan Sekarang</a>
                </div>
            </template>

            <template x-for="order in orders" :key="order.id">
                <a :href="`/orders/${order.id}`" class="block bg-white rounded-2xl p-4 border border-stone-100 shadow-sm hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-stone-800" x-text="`Pesanan #${order.id}`"></p>
                            <p class="text-xs text-stone-400 mt-0.5" x-text="formatDate(order.created_at)"></p>
                        </div>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full" :class="statusClass(order.status)" x-text="statusLabel(order.status)"></span>
                    </div>

                    <div class="mt-3 flex items-center justify-between text-sm">
                        <span class="text-stone-500" x-text="`${order.items_count} item`"></span>
                        <span class="font-bold text-amber-600" x-text="formatPrice(order.total)"></span>
                    </div>

                    <div class="mt-2 text-xs text-stone-500" x-show="order.items && order.items.length > 0">
                        <span x-text="order.items.slice(0, 2).map(i => `${i.quantity}x ${i.menu ? i.menu.name : 'Item'}`).join(' • ')"></span>
                    </div>
                </a>
            </template>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
function historyApp() {
    return {
        customer: null,
        orders: [],
        loading: true,

        async init() {
            const cached = localStorage.getItem('kafe_customer');
            if (!cached) {
                window.location.href = '/';
                return;
            }

            this.customer = JSON.parse(cached);
            await this.loadOrders();
        },

        async loadOrders() {
            this.loading = true;
            try {
                const res = await fetch(`/api/customers/${this.customer.id}/orders`);
                this.orders = await res.json();
            } catch (e) {
                showToast('Gagal memuat riwayat pesanan.', 'error');
            } finally {
                this.loading = false;
            }
        },

        statusLabel(status) {
            const labels = {
                pending: 'Menunggu',
                reviewing: 'Ditinjau',
                confirmed: 'Dikonfirmasi',
                ready: 'Siap Diambil',
                completed: 'Selesai',
                cancelled: 'Dibatalkan',
            };
            return labels[status] || status;
        },

        statusClass(status) {
            const classes = {
                pending: 'bg-blue-100 text-blue-700',
                reviewing: 'bg-amber-100 text-amber-700',
                confirmed: 'bg-purple-100 text-purple-700',
                ready: 'bg-emerald-100 text-emerald-700',
                completed: 'bg-stone-100 text-stone-600',
                cancelled: 'bg-red-100 text-red-700',
            };
            return classes[status] || 'bg-stone-100 text-stone-600';
        },

        formatPrice(val) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
            }).format(val);
        },

        formatDate(ts) {
            return new Date(ts).toLocaleString('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'short',
            });
        },
    };
}
</script>
@endpush
