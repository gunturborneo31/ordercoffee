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
                <a href="{{ route('barista.menus') }}" class="px-3 py-1.5 bg-stone-800 text-stone-200 rounded-xl text-xs font-medium hover:bg-stone-700 transition">
                    Kelola Menu
                </a>
                <a href="{{ route('barista.categories') }}" class="px-3 py-1.5 bg-stone-800 text-stone-200 rounded-xl text-xs font-medium hover:bg-stone-700 transition">
                    Kategori
                </a>
                <button @click="showSettings = true" class="p-2 text-stone-400 hover:text-white transition" title="Pengaturan Notifikasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
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

        {{-- Status + date filter --}}
        <div class="max-w-3xl mx-auto px-4 pb-3 flex justify-between items-center space-y-3">
            <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                <template x-for="filter in filters" :key="filter.value">
                    <button @click="activeFilter = filter.value"
                        :class="activeFilter === filter.value ? 'bg-amber-500 text-white' : 'bg-stone-800 text-stone-400 hover:bg-stone-700'"
                        class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition">
                        <span x-text="filter.label"></span>
                    </button>
                </template>
            </div>

            <div class="flex  sm:items-center gap-2">
                <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                    <button @click="setDateFilter('all')"
                        :class="dateFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-stone-800 text-stone-400 hover:bg-stone-700'"
                        class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition">
                        Semua Tanggal
                    </button>
                    <button @click="setDateFilter('today')"
                        :class="dateFilter === 'today' ? 'bg-blue-600 text-white' : 'bg-stone-800 text-stone-400 hover:bg-stone-700'"
                        class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition">
                        Hari Ini
                    </button>
                    {{-- <button @click="setDateFilter('yesterday')"
                        :class="dateFilter === 'yesterday' ? 'bg-blue-600 text-white' : 'bg-stone-800 text-stone-400 hover:bg-stone-700'"
                        class="px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition">
                        Kemarin
                    </button> --}}
                </div>

                <div class="flex items-center gap-2 sm:ml-auto">
                    <input type="date" x-model="selectedDate" @change="dateFilter = selectedDate ? 'custom' : 'all'"
                        class="px-3 py-2 bg-stone-800 border border-stone-700 rounded-xl text-sm text-stone-200 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    <button x-show="selectedDate" x-cloak @click="clearDateFilter()"
                        class="px-3 py-2 bg-stone-800 hover:bg-stone-700 text-stone-300 rounded-xl text-sm transition whitespace-nowrap">
                        Reset
                    </button>
                </div>
            </div>
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

                {{-- Delivery location --}}
                {{-- <template x-if="order.delivery_location">
                    <div class="px-5 pb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-xs text-amber-400 font-medium" x-text="order.delivery_location"></span>
                    </div>
                </template> --}}

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
                        <div class="flex gap-2 flex-1">
                            <button @click="updateStatus(order.id, 'confirmed')"
                                class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-medium transition">
                                ✓ Konfirmasi
                            </button>
                            <button @click="updateStatus(order.id, 'cancelled')"
                                class="flex-1 py-2.5 bg-stone-700 hover:bg-red-900/50 text-stone-300 hover:text-red-300 rounded-xl text-sm font-medium transition">
                                ✕ Batalkan
                            </button>
                        </div>
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

    {{-- Settings Modal --}}
    <div x-show="showSettings" x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
        @@keydown.escape.window="showSettings = false">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showSettings = false"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-md bg-stone-800 rounded-2xl border border-stone-700 shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-700">
                <h2 class="text-base font-bold text-white">🔔 Pengaturan Notifikasi</h2>
                <button @click="showSettings = false" class="p-1.5 hover:bg-stone-700 rounded-lg transition text-stone-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div class="rounded-xl border border-stone-700 bg-stone-700/40 p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-white">Notifikasi Desktop</p>
                            <p class="text-xs text-stone-400 mt-1">Tetap muncul sebagai notifikasi sistem saat tab browser ada di background, selama browser masih berjalan.</p>
                        </div>
                        <div class="px-2.5 py-1 rounded-full text-xs font-semibold"
                            :class="notificationPermission === 'granted'
                                ? 'bg-emerald-500/20 text-emerald-300'
                                : notificationPermission === 'denied'
                                    ? 'bg-red-500/20 text-red-300'
                                    : 'bg-amber-500/20 text-amber-300'"
                            x-text="notificationPermission === 'granted' ? 'Aktif' : notificationPermission === 'denied' ? 'Diblokir' : 'Belum Izin'"></div>
                    </div>

                    <label class="flex items-center justify-between gap-3 cursor-pointer">
                        <div>
                            <p class="text-sm text-stone-200">Tampilkan notifikasi desktop</p>
                            <p class="text-xs text-stone-500">Gunakan notifikasi browser saat ada pesanan baru.</p>
                        </div>
                        <input type="checkbox" x-model="desktopNotificationsEnabled" @change="toggleDesktopNotifications"
                            class="h-5 w-5 rounded border-stone-600 bg-stone-800 text-amber-500 focus:ring-amber-400">
                    </label>

                    <div class="flex gap-2">
                        <button @click="requestNotificationPermission()"
                            class="flex-1 py-2.5 bg-stone-800 hover:bg-stone-700 text-stone-200 rounded-xl text-sm font-medium transition">
                            Minta Izin Notifikasi
                        </button>
                        <button @click="testDesktopNotification()" :disabled="notificationPermission !== 'granted' || !desktopNotificationsEnabled"
                            class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-500 disabled:bg-stone-700 disabled:text-stone-500 text-white rounded-xl text-sm font-medium transition">
                            Test Desktop
                        </button>
                    </div>
                </div>

                {{-- Volume --}}
                <div>
                    <label class="text-xs font-semibold text-stone-400 uppercase tracking-wider">Volume</label>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-stone-500 text-sm">🔈</span>
                        <input type="range" min="0" max="1" step="0.05" x-model="notifVolume"
                            class="flex-1 accent-amber-500" @@change="saveSettings">
                        <span class="text-stone-400 text-sm w-8 text-right" x-text="Math.round(notifVolume * 100) + '%'"></span>
                        <span class="text-stone-500 text-sm">🔊</span>
                    </div>
                </div>

                {{-- Sound options --}}
                <div>
                    <label class="text-xs font-semibold text-stone-400 uppercase tracking-wider">Pilih Suara</label>
                    <div class="grid grid-cols-1 gap-2 mt-2">
                        <template x-for="sound in soundOptions" :key="sound.id">
                            <div class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                                :class="selectedSound === sound.id
                                    ? 'border-amber-500 bg-amber-500/10'
                                    : 'border-stone-700 bg-stone-700/50 hover:border-stone-500'"
                                @click="selectedSound = sound.id; saveSettings()">
                                <span class="text-xl w-8 text-center flex-shrink-0" x-text="sound.icon"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-white" x-text="sound.name"></p>
                                    <p class="text-xs text-stone-400 mt-0.5" x-text="sound.desc"></p>
                                </div>
                                <button @click.stop="previewSound(sound.id)"
                                    class="flex-shrink-0 p-2 bg-stone-600 hover:bg-amber-500 text-stone-300 hover:text-white rounded-lg transition text-xs">
                                    ▶
                                </button>
                                <div x-show="selectedSound === sound.id" class="w-4 h-4 rounded-full bg-amber-500 flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <button @click="playNotifSound(); showToast('Suara diputar!', 'success')"
                    class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-white font-semibold rounded-xl transition text-sm">
                    🔔 Test Suara Sekarang
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function baristaApp() {
    return {
        orders: [],
        activeFilter: 'all',
        dateFilter: 'all',
        selectedDate: '',
        loading: true,
        newOrderCount: 0,
        lastOrderCount: 0,
        knownOrderIds: [],
        showSettings: false,
        selectedSound: 'chime',
        notifVolume: 0.7,
        desktopNotificationsEnabled: false,
        notificationPermission: 'default',
        filters: [
            { value: 'all', label: 'Semua' },
            { value: 'pending', label: '⏳ Baru' },
            // { value: 'confirmed', label: '💳 Konfirmasi' },
            { value: 'ready', label: '✅ Siap' },
        ],
        soundOptions: [
            { id: 'chime',    icon: '🔔', name: 'Lonceng Kafe',    desc: 'Nada lonceng klasik, jernih & ramah' },
            { id: 'ding3',    icon: '✨', name: 'Triple Ding',      desc: 'Tiga ketukan manis berurutan' },
            { id: 'cash',     icon: '💰', name: 'Kasir Berbunyi',   desc: 'Nuansa koin — pesanan = uang masuk!' },
            { id: 'arcade',   icon: '🎮', name: 'Arcade Up!',       desc: 'Nada naik ala game retro' },
            { id: 'alert',    icon: '🚨', name: 'Sirine Pendek',    desc: 'Nyaring & cepat, tidak bisa dilewatkan' },
            { id: 'marimba',  icon: '🎶', name: 'Marimba',          desc: 'Melodi kayu yang hangat & catchy' },
            { id: 'beepboop', icon: '🤖', name: 'Bip Bup Robot',    desc: 'Nada sci-fi yang unik & mengundang perhatian' },
            { id: 'fanfare',  icon: '🎺', name: 'Fanfare Mini',     desc: 'Selamat datang! Heroik & meriah' },
        ],

        get activeOrders() { return this.orders; },

        get filteredOrders() {
            return this.orders.filter(order => {
                const statusMatch = this.activeFilter === 'all' || order.status === this.activeFilter;
                const dateMatch = this.matchesDateFilter(order.created_at);

                return statusMatch && dateMatch;
            });
        },

        async init() {
            const saved = localStorage.getItem('barista_notif_settings');
            if (saved) {
                try {
                    const s = JSON.parse(saved);
                    this.selectedSound = s.sound ?? 'chime';
                    this.notifVolume   = s.volume ?? 0.7;
                    this.desktopNotificationsEnabled = s.desktopNotificationsEnabled ?? false;
                } catch(e) {}
            }

            this.notificationPermission = this.getNotificationPermission();
            await this.loadOrders();
            this.knownOrderIds = this.orders.map(order => order.id);
            this.lastOrderCount = this.orders.length;
            setInterval(() => this.loadOrders(), 5000);
        },

        saveSettings() {
            localStorage.setItem('barista_notif_settings', JSON.stringify({
                sound: this.selectedSound,
                volume: parseFloat(this.notifVolume),
                desktopNotificationsEnabled: this.desktopNotificationsEnabled,
            }));
        },

        getNotificationPermission() {
            return 'Notification' in window ? Notification.permission : 'unsupported';
        },

        async requestNotificationPermission() {
            if (!('Notification' in window)) {
                showToast('Browser ini tidak mendukung notifikasi desktop.', 'error');
                return;
            }

            const permission = await Notification.requestPermission();
            this.notificationPermission = permission;

            if (permission === 'granted') {
                this.desktopNotificationsEnabled = true;
                this.saveSettings();
                showToast('Notifikasi desktop diaktifkan.', 'success');
            } else if (permission === 'denied') {
                this.desktopNotificationsEnabled = false;
                this.saveSettings();
                showToast('Izin notifikasi ditolak oleh browser.', 'warning');
            }
        },

        toggleDesktopNotifications() {
            if (this.desktopNotificationsEnabled && this.notificationPermission !== 'granted') {
                this.desktopNotificationsEnabled = false;
                this.requestNotificationPermission();
                return;
            }

            this.saveSettings();
        },

        async testDesktopNotification() {
            if (!this.canShowDesktopNotification()) {
                showToast('Aktifkan izin notifikasi desktop terlebih dulu.', 'warning');
                return;
            }

            await this.showDesktopNotification({
                title: 'Pesanan Test',
                body: 'Notifikasi background aktif di dashboard barista.',
                url: '/barista',
                tag: 'barista-test',
            });
            showToast('Notifikasi desktop dikirim.', 'success');
        },

        canShowDesktopNotification() {
            return this.desktopNotificationsEnabled && this.notificationPermission === 'granted';
        },

        async showDesktopNotification({ title, body, url, tag }) {
            if (!this.canShowDesktopNotification()) return;

            const options = {
                body,
                icon: '/icons/icon-192.png',
                badge: '/icons/icon-192.png',
                tag,
                renotify: true,
                requireInteraction: false,
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

        async notifyNewOrders(newOrders) {
            if (!newOrders.length) return;

            const newestOrder = newOrders[0];
            const count = newOrders.length;
            const title = count === 1 ? 'Pesanan Baru Masuk' : `${count} Pesanan Baru Masuk`;
            const body = count === 1
                ? `#${newestOrder.id} · ${newestOrder.customer?.name || 'Customer'} · ${this.formatPrice(newestOrder.total)}`
                : `Pesanan terbaru #${newestOrder.id} dari ${newestOrder.customer?.name || 'Customer'}`;

            this.playNotifSound();
            showToast(`${count} pesanan baru masuk!`, 'info');

            if (document.hidden || this.canShowDesktopNotification()) {
                await this.showDesktopNotification({
                    title,
                    body,
                    url: `/barista/orders/${newestOrder.id}`,
                    tag: `barista-order-${newestOrder.id}`,
                });
            }
        },

        previewSound(id) {
            const prev = this.selectedSound;
            this.selectedSound = id;
            this.playNotifSound();
            this.selectedSound = prev;
        },

        setDateFilter(filter) {
            this.dateFilter = filter;
            if (filter === 'today') {
                this.selectedDate = this.toDateInputValue(new Date());
            } else if (filter === 'yesterday') {
                const yesterday = new Date();
                yesterday.setDate(yesterday.getDate() - 1);
                this.selectedDate = this.toDateInputValue(yesterday);
            } else if (filter === 'all') {
                this.selectedDate = '';
            }
        },

        clearDateFilter() {
            this.dateFilter = 'all';
            this.selectedDate = '';
        },

        matchesDateFilter(timestamp) {
            if (this.dateFilter === 'all' || !timestamp) {
                return true;
            }

            const orderDate = this.toDateInputValue(new Date(timestamp));

            if (this.dateFilter === 'custom') {
                return !!this.selectedDate && orderDate === this.selectedDate;
            }

            return !!this.selectedDate && orderDate === this.selectedDate;
        },

        toDateInputValue(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        },

        async loadOrders() {
            try {
                const res = await fetch('/barista/api/orders');
                const orders = await res.json();

                const pendingNew = orders.filter(o => o.status === 'pending').length;

                const newPendingOrders = this.knownOrderIds.length
                    ? orders.filter(order => order.status === 'pending' && !this.knownOrderIds.includes(order.id))
                    : [];

                this.newOrderCount = pendingNew;

                if (newPendingOrders.length > 0) {
                    await this.notifyNewOrders(newPendingOrders);
                }

                this.lastOrderCount = orders.length;
                this.orders = orders;
                this.knownOrderIds = orders.map(order => order.id);
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
                const vol = parseFloat(this.notifVolume);
                const id  = this.selectedSound;

                const osc = (freq, type, start, dur, volMul = 1) => {
                    const o = ctx.createOscillator();
                    const g = ctx.createGain();
                    o.connect(g); g.connect(ctx.destination);
                    o.type = type; o.frequency.value = freq;
                    const t = ctx.currentTime + start;
                    g.gain.setValueAtTime(vol * volMul, t);
                    g.gain.exponentialRampToValueAtTime(0.001, t + dur);
                    o.start(t); o.stop(t + dur);
                };

                const slide = (f1, f2, type, start, dur, volMul = 1) => {
                    const o = ctx.createOscillator();
                    const g = ctx.createGain();
                    o.connect(g); g.connect(ctx.destination);
                    o.type = type;
                    const t = ctx.currentTime + start;
                    o.frequency.setValueAtTime(f1, t);
                    o.frequency.linearRampToValueAtTime(f2, t + dur);
                    g.gain.setValueAtTime(vol * volMul, t);
                    g.gain.exponentialRampToValueAtTime(0.001, t + dur);
                    o.start(t); o.stop(t + dur);
                };

                if (id === 'chime') {
                    // Lonceng Kafe — sine waves, sweet ascending chord
                    [[523, 0], [659, 0.12], [784, 0.24], [1047, 0.38]].forEach(([f, t]) => osc(f, 'sine', t, 0.6));
                } else if (id === 'ding3') {
                    // Triple Ding
                    [0, 0.2, 0.4].forEach(t => osc(1318, 'sine', t, 0.35));
                } else if (id === 'cash') {
                    // Kasir — quick high ping + low thud
                    osc(1760, 'sine',   0,    0.15, 0.9);
                    osc(880,  'sine',   0.05, 0.12, 0.6);
                    osc(220,  'square', 0.1,  0.08, 0.4);
                    osc(1760, 'sine',   0.22, 0.15, 0.8);
                } else if (id === 'arcade') {
                    // Arcade Up! — rising arpeggio
                    [[262,0],[330,0.08],[392,0.16],[523,0.24],[659,0.32],[784,0.40],[1047,0.50]].forEach(([f,t]) => osc(f, 'square', t, 0.12, 0.5));
                } else if (id === 'alert') {
                    // Sirine pendek — rapid alternating
                    for (let i = 0; i < 6; i++) {
                        osc(i % 2 === 0 ? 1200 : 900, 'sawtooth', i * 0.1, 0.09, 0.6);
                    }
                } else if (id === 'marimba') {
                    // Marimba — triangle wave with warm decay
                    [[523,0],[659,0.15],[784,0.30],[659,0.45],[880,0.60]].forEach(([f,t]) => osc(f, 'triangle', t, 0.4, 0.85));
                } else if (id === 'beepboop') {
                    // Bip Bup Robot
                    slide(300, 800, 'square', 0,   0.18, 0.6);
                    slide(800, 200, 'square', 0.2, 0.18, 0.6);
                    slide(400, 900, 'square', 0.4, 0.18, 0.6);
                } else if (id === 'fanfare') {
                    // Fanfare mini — bold brass feel
                    const seq = [[523,0,0.1],[523,0.1,0.1],[523,0.2,0.12],[659,0.34,0.18],[784,0.54,0.35]];
                    seq.forEach(([f,t,d]) => osc(f, 'sawtooth', t, d, 0.5));
                    seq.forEach(([f,t,d]) => osc(f * 2, 'sine', t, d, 0.2));
                } else {
                    // Fallback
                    [440, 550, 660].forEach((f, i) => osc(f, 'sine', i * 0.15, 0.3));
                }
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
