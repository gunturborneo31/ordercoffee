@extends('layouts.app')

@section('title', 'Pesanan #' . $order->id . ' — Kafe Kita')

@section('content')
<div class="min-h-screen bg-stone-50 flex flex-col" x-data="orderApp({{ $order->id }}, '{{ $order->customer->name }}')" x-init="init()">

    {{-- Header --}}
    <header class="sticky top-0 z-30 bg-white border-b border-stone-100 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
            <a href="/menu" class="p-2 hover:bg-stone-100 rounded-xl transition text-stone-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-base font-bold text-stone-900">Pesanan #{{ $order->id }}</h1>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                        :class="statusClass(order.status)"
                        x-text="statusLabel(order.status)"></span>
                </div>
            </div>
            <div x-show="unreadCount > 0" x-cloak
                class="flex items-center gap-1 bg-red-50 text-red-600 px-2.5 py-1 rounded-full text-xs font-bold">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/>
                </svg>
                <span x-text="unreadCount"></span>
            </div>
        </div>
    </header>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto max-w-2xl w-full mx-auto px-4 py-4 space-y-4 pb-32">

        {{-- Order Summary --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-stone-100">
            <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wider mb-3">Rincian Pesanan</h2>
            <div class="space-y-2">
                <template x-for="item in order.items" :key="item.id">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-stone-800" x-text="`${item.quantity}× ${item.menu ? item.menu.name : 'Item'}`"></span>
                            <span x-show="item.note" class="block text-xs text-stone-400 mt-0.5" x-text="`📝 ${item.note}`"></span>
                        </div>
                        <span class="text-sm font-semibold text-stone-700" x-text="formatPrice(item.price * item.quantity)"></span>
                    </div>
                </template>
            </div>
            <div class="border-t border-stone-100 mt-3 pt-3 flex items-center justify-between">
                <span class="text-sm font-semibold text-stone-700">Total</span>
                <span class="text-lg font-bold text-amber-600" x-text="formatPrice(order.total)"></span>
            </div>
        </div>

        {{-- QRIS Payment --}}
        <template x-if="order.status === 'confirmed' && order.qris_string">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-100 text-center">
                <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wider mb-3">Pembayaran QRIS</h2>
                <p class="text-sm text-stone-500 mb-4">Scan QR di bawah dengan aplikasi bank atau e-wallet kamu</p>
                <div class="flex justify-center mb-4">
                    <canvas id="qr-canvas" class="rounded-xl shadow-md"></canvas>
                </div>
                <p class="text-xs text-stone-400">Nominal sudah otomatis tercantum</p>
                <div class="mt-3 bg-amber-50 rounded-xl px-4 py-2">
                    <p class="text-sm font-bold text-amber-700" x-text="formatPrice(order.total)"></p>
                </div>
            </div>
        </template>

        {{-- Completed --}}
        <template x-if="order.status === 'completed'">
            <div class="bg-emerald-50 rounded-2xl p-5 text-center border border-emerald-100">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-emerald-800">Pesanan Selesai! 🎉</h3>
                <p class="text-sm text-emerald-600 mt-1">Terima kasih telah berkunjung ke Kafe Kita.</p>
                <a href="/menu" class="mt-4 inline-block px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition">Pesan Lagi</a>
            </div>
        </template>

        {{-- Status Info --}}
        <template x-if="order.status === 'pending'">
            <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-blue-800">Menunggu konfirmasi barista</p>
                        <p class="text-xs text-blue-600">Barista sedang mengecek pesananmu...</p>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="order.status === 'reviewing'">
            <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">👀</span>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Barista sedang mereview pesananmu</p>
                        <p class="text-xs text-amber-600">Tunggu sebentar ya...</p>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="order.status === 'ready'">
            <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">✅</span>
                    <div>
                        <p class="text-sm font-semibold text-emerald-800">Pesanan siap diambil!</p>
                        <p class="text-xs text-emerald-600">Silakan ke counter untuk mengambil pesananmu.</p>
                    </div>
                </div>
            </div>
        </template>

        {{-- Chat --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-stone-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-stone-700">💬 Chat dengan Barista</h2>
                <button @click="loadMessages" class="text-stone-400 hover:text-stone-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>

            <div id="chat-messages" class="h-60 overflow-y-auto p-4 space-y-3">
                <template x-for="msg in messages" :key="msg.id">
                    <div :class="msg.sender_type === 'customer' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.sender_type === 'customer' ? 'bg-amber-500 text-white rounded-2xl rounded-tr-sm' : 'bg-stone-100 text-stone-800 rounded-2xl rounded-tl-sm'"
                            class="max-w-xs px-4 py-2.5">
                            <p class="text-sm" x-text="msg.content"></p>
                            <p class="text-xs mt-1 opacity-60" x-text="formatTime(msg.created_at)"></p>
                        </div>
                    </div>
                </template>
                <template x-if="messages.length === 0">
                    <p class="text-center text-stone-400 text-sm py-4">Belum ada pesan. Mulai chat dengan barista!</p>
                </template>
            </div>

            <div class="px-4 py-3 border-t border-stone-100 flex gap-2">
                <input type="text" x-model="newMessage" @keydown.enter="sendMessage"
                    placeholder="Ketik pesan..."
                    class="flex-1 px-3 py-2 rounded-xl border border-stone-200 focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm text-stone-800 placeholder-stone-300">
                <button @click="sendMessage" :disabled="!newMessage.trim()"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-400 disabled:bg-stone-200 text-white rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function orderApp(orderId, customerName) {
    return {
        orderId,
        customerName,
        order: @json($order->load(['items.menu', 'customer', 'messages'])),
        messages: @json($order->messages()->orderBy('created_at')->get()),
        newMessage: '',
        unreadCount: 0,
        pollInterval: null,

        async init() {
            this.renderQris();
            this.scrollChat();
            // Poll every 5 seconds
            this.pollInterval = setInterval(() => this.poll(), 5000);
        },

        async poll() {
            try {
                const res = await fetch(`/api/orders/${this.orderId}`);
                const data = await res.json();
                const prevStatus = this.order.status;
                this.order = data;
                if (prevStatus !== data.status) {
                    this.renderQris();
                    showToast(`Status pesanan: ${this.statusLabel(data.status)}`, 'info');
                }
                await this.loadMessages();
            } catch(e) {}
        },

        async loadMessages() {
            try {
                const res = await fetch(`/api/orders/${this.orderId}/messages`);
                const msgs = await res.json();
                const prevCount = this.messages.length;
                this.messages = msgs;
                this.unreadCount = msgs.filter(m => m.sender_type === 'barista' && !m.is_read).length;
                if (msgs.length > prevCount) {
                    this.scrollChat();
                    if (msgs[msgs.length - 1]?.sender_type === 'barista') {
                        this.playNotifSound();
                        showToast('Pesan baru dari barista!', 'info');
                    }
                }
                // Mark read
                if (this.unreadCount > 0) {
                    fetch(`/api/orders/${this.orderId}/messages/read`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    this.unreadCount = 0;
                }
            } catch(e) {}
        },

        async sendMessage() {
            if (!this.newMessage.trim()) return;
            const content = this.newMessage;
            this.newMessage = '';
            try {
                await fetch(`/api/orders/${this.orderId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        sender_type: 'customer',
                        sender_name: this.customerName,
                        content,
                    }),
                });
                await this.loadMessages();
            } catch(e) {
                showToast('Gagal mengirim pesan.', 'error');
            }
        },

        renderQris() {
            this.$nextTick(() => {
                if (this.order.status === 'confirmed' && this.order.qris_string) {
                    const canvas = document.getElementById('qr-canvas');
                    if (canvas && typeof QRCode !== 'undefined') {
                        QRCode.toCanvas(canvas, this.order.qris_string, { width: 240, margin: 2 }, () => {});
                    }
                }
            });
        },

        scrollChat() {
            this.$nextTick(() => {
                const el = document.getElementById('chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        playNotifSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 880;
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.5);
            } catch(e) {}
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
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
        },

        formatTime(ts) {
            return new Date(ts).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
    };
}
</script>
@endpush
