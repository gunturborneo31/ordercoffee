@extends('layouts.app')

@section('title', 'Kelola Pesanan #' . $order->id . ' — Barista')

@section('content')
<div class="min-h-screen bg-stone-900 flex flex-col" x-data="baristaOrderApp({{ $order->id }})" x-init="init()">

    {{-- Header --}}
    <header class="sticky top-0 z-30 bg-stone-900 border-b border-stone-700">
        <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
            <a href="/barista" class="p-2 hover:bg-stone-800 rounded-xl transition text-stone-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-base font-bold text-white">Pesanan #{{ $order->id }}</h1>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                        :class="statusClass(order.status)"
                        x-text="statusLabel(order.status)"></span>
                    <span class="text-xs text-stone-500" x-text="order.customer?.name"></span>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto max-w-2xl w-full mx-auto px-4 py-4 space-y-4 pb-32">

        {{-- Order Items --}}
        <div class="bg-stone-800 rounded-2xl p-5 border border-stone-700">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-stone-300 uppercase tracking-wider">Rincian Pesanan</h2>
                <button x-show="canEdit" @click="editMode = !editMode" x-cloak
                    :class="editMode ? 'bg-amber-500 text-white' : 'bg-stone-700 text-stone-300'"
                    class="text-xs px-3 py-1.5 rounded-lg transition">
                    <span x-text="editMode ? '✕ Batal Edit' : '✏️ Edit'"></span>
                </button>
            </div>

            {{-- View mode --}}
            <template x-if="!editMode">
                <div class="space-y-2">
                    <template x-for="item in order.items" :key="item.id">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-stone-200" x-text="`${item.quantity}× ${item.menu?.name}`"></span>
                                <span x-show="item.note" class="block text-xs text-stone-500 mt-0.5" x-text="`📝 ${item.note}`"></span>
                            </div>
                            <span class="text-sm font-semibold text-stone-300" x-text="formatPrice(item.price * item.quantity)"></span>
                        </div>
                    </template>
                    <div class="border-t border-stone-700 mt-3 pt-3 flex items-center justify-between">
                        <span class="text-sm font-semibold text-stone-300">Total</span>
                        <span class="text-lg font-bold text-amber-400" x-text="formatPrice(order.total)"></span>
                    </div>
                </div>
            </template>

            {{-- Edit mode --}}
            <template x-if="editMode">
                <div class="space-y-3">
                    <template x-for="(item, idx) in editItems" :key="idx">
                        <div class="bg-stone-700/50 rounded-xl p-3">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="text-sm font-medium text-stone-200" x-text="item.menu_name"></span>
                                <button @click="editItems.splice(idx, 1)" class="text-red-400 hover:text-red-300 text-xs px-2 py-1 bg-red-900/30 rounded-lg">Hapus</button>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1">
                                    <button @click="item.quantity = Math.max(1, item.quantity - 1)" class="w-7 h-7 bg-stone-600 rounded-lg text-white flex items-center justify-center hover:bg-stone-500 transition">−</button>
                                    <span class="w-8 text-center text-sm font-bold text-white" x-text="item.quantity"></span>
                                    <button @click="item.quantity++" class="w-7 h-7 bg-amber-500 rounded-lg text-white flex items-center justify-center hover:bg-amber-400 transition">+</button>
                                </div>
                                <input type="text" x-model="item.note" placeholder="Catatan..."
                                    class="flex-1 text-xs px-3 py-2 bg-stone-700 border border-stone-600 rounded-lg text-stone-300 placeholder-stone-500 focus:outline-none focus:ring-1 focus:ring-amber-400">
                            </div>
                        </div>
                    </template>

                    <div class="border-t border-stone-700 pt-3">
                        <p class="text-sm font-bold text-amber-400 text-right" x-text="`Total: ${formatPrice(editTotal)}`"></p>
                    </div>

                    <button @click="saveEdit" :disabled="editSaving"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-700 text-white font-semibold rounded-xl transition">
                        <span x-text="editSaving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                </div>
            </template>
        </div>

        {{-- Menu Availability --}}
        <div class="bg-stone-800 rounded-2xl p-5 border border-stone-700">
            <h2 class="text-sm font-semibold text-stone-300 uppercase tracking-wider mb-3">Ketersediaan Menu</h2>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                <template x-for="menu in allMenus" :key="menu.id">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-stone-300" x-text="menu.name"></span>
                        <button @click="toggleMenuAvailability(menu)"
                            :class="menu.is_available ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-stone-600 hover:bg-stone-500'"
                            class="text-xs px-3 py-1.5 rounded-lg text-white font-medium transition">
                            <span x-text="menu.is_available ? 'Tersedia' : 'Habis'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="bg-stone-800 rounded-2xl p-5 border border-stone-700 space-y-3">
            <h2 class="text-sm font-semibold text-stone-300 uppercase tracking-wider mb-3">Aksi Pesanan</h2>

            <template x-if="order.status === 'pending'">
                <button @click="updateStatus('reviewing')"
                    class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-white font-semibold rounded-xl transition">
                    👀 Mulai Tinjau Pesanan
                </button>
            </template>

            <template x-if="order.status === 'reviewing'">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-stone-400 uppercase tracking-wider mb-2">QRIS Statis (opsional)</label>
                        <textarea x-model="qrisStatic" rows="3"
                            placeholder="Tempel string QRIS statis kamu di sini..."
                            class="w-full text-xs px-3 py-2 bg-stone-700 border border-stone-600 rounded-xl text-stone-300 placeholder-stone-500 focus:outline-none focus:ring-1 focus:ring-amber-400 resize-none"></textarea>
                        <p class="text-xs text-stone-500 mt-1">Nominal akan otomatis disisipkan ke QRIS</p>
                    </div>
                    <button @click="confirmOrder"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl transition">
                        ✅ Konfirmasi & Generate QRIS
                    </button>
                    <button @click="updateStatus('cancelled')"
                        class="w-full py-3 bg-stone-700 hover:bg-red-900/50 text-stone-300 hover:text-red-300 font-semibold rounded-xl transition">
                        ✕ Batalkan Pesanan
                    </button>
                </div>
            </template>

            <template x-if="order.status === 'confirmed'">
                <div class="space-y-3">
                    <div class="bg-stone-700/50 rounded-xl p-4 text-center">
                        <p class="text-sm text-stone-300 mb-3">QR Code telah dikirim ke customer</p>
                        <canvas id="qr-canvas-barista" class="mx-auto rounded-xl shadow-md"></canvas>
                    </div>
                    <button @click="updateStatus('ready')"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl transition">
                        ☕ Tandai Siap Diambil
                    </button>
                </div>
            </template>

            <template x-if="order.status === 'ready'">
                <button @click="updateStatus('completed')"
                    class="w-full py-3 bg-emerald-700 hover:bg-emerald-600 text-white font-semibold rounded-xl transition">
                    🎉 Pesanan Selesai
                </button>
            </template>
        </div>

        {{-- Chat --}}
        <div class="bg-stone-800 rounded-2xl border border-stone-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-stone-700 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-stone-300">💬 Chat dengan Customer</h2>
                <div x-show="unreadCount > 0" x-cloak class="bg-red-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center" x-text="unreadCount"></div>
            </div>

            <div id="barista-chat" class="h-64 overflow-y-auto p-4 space-y-3">
                <template x-for="msg in messages" :key="msg.id">
                    <div :class="msg.sender_type === 'barista' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.sender_type === 'barista' ? 'bg-amber-500 text-white rounded-2xl rounded-tr-sm' : 'bg-stone-700 text-stone-200 rounded-2xl rounded-tl-sm'"
                            class="max-w-xs px-4 py-2.5">
                            <p class="text-xs font-semibold mb-0.5 opacity-70" x-text="msg.sender_name"></p>
                            <p class="text-sm" x-text="msg.content"></p>
                            <p class="text-xs mt-1 opacity-50" x-text="formatTime(msg.created_at)"></p>
                        </div>
                    </div>
                </template>
                <template x-if="messages.length === 0">
                    <p class="text-center text-stone-500 text-sm py-4">Belum ada pesan.</p>
                </template>
            </div>

            <div class="px-4 py-3 border-t border-stone-700 flex gap-2">
                <input type="text" x-model="newMessage" @keydown.enter="sendMessage"
                    placeholder="Ketik pesan ke customer..."
                    class="flex-1 px-3 py-2 rounded-xl bg-stone-700 border border-stone-600 text-stone-200 text-sm placeholder-stone-500 focus:outline-none focus:ring-1 focus:ring-amber-400">
                <button @click="sendMessage" :disabled="!newMessage.trim()"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-400 disabled:bg-stone-600 text-white rounded-xl transition">
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
function baristaOrderApp(orderId) {
    return {
        orderId,
        order: @json($order->load(['items.menu', 'customer', 'messages'])),
        messages: @json($order->messages()->orderBy('created_at')->get()),
        allMenus: [],
        editMode: false,
        editItems: [],
        editSaving: false,
        newMessage: '',
        qrisStatic: '',
        unreadCount: 0,

        get canEdit() {
            return ['pending', 'reviewing'].includes(this.order.status);
        },

        get editTotal() {
            return this.editItems.reduce((sum, item) => sum + item.price * item.quantity, 0);
        },

        async init() {
            this.setupEditItems();
            this.renderQris();
            this.scrollChat();
            await this.loadMenus();
            setInterval(() => this.poll(), 5000);
        },

        setupEditItems() {
            this.editItems = this.order.items.map(item => ({
                menu_id: item.menu_id,
                menu_name: item.menu?.name || '',
                quantity: item.quantity,
                price: item.price,
                note: item.note || '',
            }));
        },

        async loadMenus() {
            try {
                const res = await fetch('/api/menus');
                this.allMenus = await res.json();
            } catch(e) {}
        },

        async poll() {
            try {
                const [orderRes, msgRes] = await Promise.all([
                    fetch(`/barista/api/orders/${this.orderId}`),
                    fetch(`/api/orders/${this.orderId}/messages`),
                ]);
                const orderData = await orderRes.json();
                const msgs = await msgRes.json();

                const prevStatus = this.order.status;
                this.order = orderData;
                if (prevStatus !== orderData.status) {
                    this.renderQris();
                }

                const prevCount = this.messages.length;
                this.messages = msgs;
                this.unreadCount = msgs.filter(m => m.sender_type === 'customer' && !m.is_read).length;

                if (msgs.length > prevCount) {
                    this.scrollChat();
                    if (msgs[msgs.length-1]?.sender_type === 'customer') {
                        this.playNotif();
                        showToast('Pesan baru dari customer!', 'info');
                    }
                }

                if (this.unreadCount > 0) {
                    fetch(`/api/orders/${this.orderId}/messages/read`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    this.unreadCount = 0;
                }
            } catch(e) {}
        },

        async updateStatus(status, extra = {}) {
            try {
                const res = await fetch(`/api/orders/${this.orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status, ...extra }),
                });
                const data = await res.json();
                if (data.success) {
                    this.order = data.order;
                    this.renderQris();
                    showToast(`Status: ${this.statusLabel(status)}`, 'success');
                }
            } catch(e) {
                showToast('Gagal memperbarui.', 'error');
            }
        },

        async confirmOrder() {
            await this.updateStatus('confirmed', { qris_static: this.qrisStatic });
        },

        async saveEdit() {
            if (this.editItems.length === 0) return;
            this.editSaving = true;
            try {
                const res = await fetch(`/api/orders/${this.orderId}/items`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ items: this.editItems }),
                });
                const data = await res.json();
                if (data.success) {
                    this.order = data.order;
                    this.editMode = false;
                    this.setupEditItems();
                    showToast('Pesanan berhasil diperbarui.', 'success');
                } else {
                    showToast(data.error || 'Gagal menyimpan.', 'error');
                }
            } catch(e) {
                showToast('Gagal menyimpan.', 'error');
            } finally {
                this.editSaving = false;
            }
        },

        async toggleMenuAvailability(menu) {
            try {
                const res = await fetch(`/api/menus/${menu.id}/availability`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ is_available: !menu.is_available }),
                });
                const data = await res.json();
                if (data.success) {
                    menu.is_available = data.menu.is_available;
                    showToast(`${menu.name}: ${menu.is_available ? 'Tersedia' : 'Habis'}`, 'success');
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
                        sender_type: 'barista',
                        sender_name: 'Barista',
                        content,
                    }),
                });
                const res = await fetch(`/api/orders/${this.orderId}/messages`);
                this.messages = await res.json();
                this.scrollChat();
            } catch(e) {
                showToast('Gagal mengirim.', 'error');
            }
        },

        renderQris() {
            this.$nextTick(() => {
                if (this.order.status === 'confirmed' && this.order.qris_string) {
                    const canvas = document.getElementById('qr-canvas-barista');
                    if (canvas && typeof QRCode !== 'undefined') {
                        QRCode.toCanvas(canvas, this.order.qris_string, { width: 200, margin: 2 }, () => {});
                    }
                }
            });
        },

        scrollChat() {
            this.$nextTick(() => {
                const el = document.getElementById('barista-chat');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        playNotif() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 660;
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.4);
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
