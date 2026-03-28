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
            <template x-if="order.delivery_location">
                <div class="flex items-center gap-2 mb-3 bg-amber-50 rounded-xl px-3 py-2">
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm text-amber-800 font-medium" x-text="order.delivery_location"></span>
                </div>
            </template>
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
        <template x-if="['confirmed', 'ready'].includes(order.status)">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-100 text-center">
                <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wider mb-3">Pembayaran QRIS</h2>
                <p class="text-sm text-stone-500 mb-4">Scan QR di bawah dengan aplikasi bank atau e-wallet kamu</p>
                <div class="flex justify-center mb-4">
                    <img :src="qrisImageUrl" alt="QRIS" class="w-60 h-60 object-contain rounded-xl shadow-md bg-white p-2"
                        @@error="onQrisImageError">
                </div>
                <p class="text-xs text-stone-400">Silakan scan QRIS statis untuk pembayaran</p>
                <div class="mt-3 bg-amber-50 rounded-xl px-4 py-2">
                    <p class="text-sm font-bold text-amber-700" x-text="formatPrice(order.total)"></p>
                </div>

                <div class="mt-5 text-left border-2 border-dashed border-amber-300 bg-amber-50/70 rounded-2xl p-4 animate-pulse">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-amber-800">Upload bukti pembayaran di sini</p>
                            <p class="text-xs text-amber-700 mt-1">Setelah transfer QRIS, kirim screenshot atau foto bukti agar barista cepat memproses pesananmu.</p>
                        </div>
                    </div>

                    <input x-ref="proofInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="uploadQrisProof($event)">

                    <div class="mt-4 flex flex-col sm:flex-row gap-2 sm:items-center">
                        <button @click="$refs.proofInput.click()" :disabled="proofUploading"
                            class="px-4 py-3 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-300 text-white font-semibold rounded-xl transition text-sm shadow-sm">
                            <span x-text="proofUploading ? 'Mengupload...' : (order.qris_proof_url ? 'Upload Ulang Bukti' : 'Pilih Bukti QRIS')"></span>
                        </button>
                        <span class="text-xs text-stone-500" x-text="selectedProofName || (order.qris_proof_url ? 'Bukti sudah terupload.' : 'Format: JPG, PNG, WEBP maks 3MB')"></span>
                    </div>

                    <template x-if="order.qris_proof_url">
                        <div class="mt-4 rounded-2xl overflow-hidden border border-amber-200 bg-white">
                            <div class="px-3 py-2 bg-amber-100 text-amber-800 text-xs font-semibold">Bukti pembayaran terkirim</div>
                            <img :src="order.qris_proof_url" alt="Bukti pembayaran QRIS" class="w-full max-h-80 object-contain bg-white">
                        </div>
                    </template>
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
        notificationPermission: 'default',
        qrisImageUrl: @json(asset('storage/' . config('app.qris_image', 'qris.jpeg'))),
        proofUploading: false,
        selectedProofName: '',

        async init() {
            this.scrollChat();
            this.notificationPermission = this.getNotificationPermission();
            await this.ensureNotificationPermission();
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
                    showToast(`Status pesanan: ${this.statusLabel(data.status)}`, 'info');
                    if (data.status === 'confirmed') {
                        this.playNotifSound();
                        await this.showDesktopNotification({
                            title: 'Pesanan Dikonfirmasi',
                            body: `Pesanan #${this.orderId} sudah dikonfirmasi barista.`,
                            url: `/orders/${this.orderId}`,
                            tag: `customer-order-confirmed-${this.orderId}`,
                        });
                    }
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
                        await this.showDesktopNotification({
                            title: 'Pesan Baru dari Barista',
                            body: msgs[msgs.length - 1]?.content || 'Ada pesan baru untuk pesananmu.',
                            url: `/orders/${this.orderId}`,
                            tag: `customer-chat-${this.orderId}`,
                        });
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

        async uploadQrisProof(event) {
            const file = event.target.files?.[0];
            if (!file) return;

            this.selectedProofName = file.name;
            this.proofUploading = true;

            try {
                const formData = new FormData();
                formData.append('proof', file);

                const res = await fetch(`/api/orders/${this.orderId}/qris-proof`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    showToast(data.message || data.error || 'Gagal upload bukti pembayaran.', 'error');
                    return;
                }

                this.order = data.order;
                showToast('Bukti pembayaran berhasil diupload.', 'success');
            } catch (e) {
                showToast('Gagal upload bukti pembayaran.', 'error');
            } finally {
                this.proofUploading = false;
                if (this.$refs.proofInput) {
                    this.$refs.proofInput.value = '';
                }
            }
        },

        onQrisImageError() {
            showToast('Gambar QRIS tidak ditemukan. Pastikan file ada di storage/app/public/qris.jpeg', 'error');
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

        getNotificationPermission() {
            return 'Notification' in window ? Notification.permission : 'unsupported';
        },

        async ensureNotificationPermission() {
            if (!('Notification' in window) || this.notificationPermission !== 'default') {
                return;
            }

            try {
                this.notificationPermission = await Notification.requestPermission();
            } catch (e) {}
        },

        canShowDesktopNotification() {
            return this.notificationPermission === 'granted';
        },

        async showDesktopNotification({ title, body, url, tag }) {
            if (!this.canShowDesktopNotification()) return;

            const options = {
                body,
                icon: '/icons/icon-192.png',
                badge: '/icons/icon-192.png',
                tag,
                renotify: true,
                data: { url },
            };

            try {
                if ('serviceWorker' in navigator) {
                    const registration = await navigator.serviceWorker.ready;
                    await registration.showNotification(title, options);
                    return;
                }

                new Notification(title, options);
            } catch (e) {}
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
