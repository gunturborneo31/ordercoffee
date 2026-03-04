<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1a1a2e">
    <meta name="description" content="Kafe Kita - Pemesanan Kopi Online">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Kafe Kita">

    <title>@yield('title', 'Kafe Kita')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- QR Code library --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    @stack('head')
</head>
<body class="h-full bg-stone-50 text-stone-900 antialiased">

    @yield('content')

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <script>
        // Global toast function
        window.showToast = function(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = {
                'success': 'bg-emerald-600',
                'error': 'bg-red-600',
                'info': 'bg-stone-800',
                'warning': 'bg-amber-600'
            };
            toast.className = `pointer-events-auto ${colors[type] || colors.info} text-white px-4 py-3 rounded-xl shadow-lg text-sm font-medium transform translate-x-full transition-transform duration-300 max-w-xs`;
            toast.textContent = message;
            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full');
            });
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };

        // PWA Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').then(reg => {
                console.log('SW registered:', reg.scope);
            }).catch(err => {
                console.log('SW error:', err);
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
