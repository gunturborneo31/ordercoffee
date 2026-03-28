@extends('layouts.app')

@section('title', 'Selamat Datang — Kafe Kita')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center p-6 bg-gradient-to-b from-stone-900 to-stone-800"
     x-data="registrationApp()"
     x-init="init()">

    <div class="mb-10 text-center">
        <div class="w-20 h-20 bg-amber-500 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white tracking-tight">Kafe Kita</h1>
        <p class="text-stone-400 mt-1 text-sm">Pesan dengan mudah, nikmati dengan santai</p>
    </div>

    <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-8">
            <h2 class="text-xl font-semibold text-stone-800 mb-1">Halo! 👋</h2>
            <p class="text-stone-500 text-sm mb-6">Masukkan nama dan nomor HP kamu untuk mulai memesan.</p>

            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-1.5">Nama</label>
                    <input type="text" x-model="form.name" placeholder="Masukkan nama lengkap"
                        class="w-full px-4 py-3 rounded-xl border border-stone-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-stone-800 placeholder-stone-300 transition"
                        required autofocus>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-1.5">Nomor HP</label>
                    <input type="tel" x-model="form.phone" placeholder="08xxxxxxxxxx"
                        class="w-full px-4 py-3 rounded-xl border border-stone-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-stone-800 placeholder-stone-300 transition"
                        required>
                </div>
                <button type="submit" :disabled="loading"
                    class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 active:bg-amber-600 disabled:bg-amber-300 text-white font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2 mt-2">
                    <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="loading ? 'Memproses...' : 'Mulai Pesan'"></span>
                </button>
            </form>
            <p x-show="errorMsg" x-text="errorMsg" class="mt-3 text-red-500 text-sm text-center" x-cloak></p>
        </div>

        <div class="px-8 pb-6 border-t border-stone-100 pt-4">
            <a href="{{ route('barista.dashboard') }}" class="flex items-center justify-center gap-2 text-stone-400 hover:text-stone-600 text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Masuk sebagai Barista
            </a>
        </div>
    </div>

    <p class="text-stone-500 text-xs mt-8">Data kamu disimpan untuk kemudahan pemesanan berikutnya 🍃</p>
</div>
@endsection

@push('scripts')
<script>
function registrationApp() {
    return {
        form: { name: '', phone: '' },
        loading: false,
        errorMsg: '',

        init() {
            const cached = localStorage.getItem('kafe_customer');
            if (cached) {
                try {
                    const customer = JSON.parse(cached);
                    fetch(`/customer/lookup?phone=${encodeURIComponent(customer.phone)}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.found) {
                                window.location.href = '/menu';
                            } else {
                                localStorage.removeItem('kafe_customer');
                            }
                        }).catch(() => {});
                } catch(e) {
                    localStorage.removeItem('kafe_customer');
                }
            }
        },

        async submitForm() {
            this.errorMsg = '';
            this.loading = true;
            try {
                const res = await fetch('/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (data.success) {
                    localStorage.setItem('kafe_customer', JSON.stringify(data.customer));
                    window.location.href = '/menu';
                } else {
                    this.errorMsg = 'Terjadi kesalahan. Coba lagi.';
                }
            } catch(e) {
                this.errorMsg = 'Koneksi bermasalah. Coba lagi.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
