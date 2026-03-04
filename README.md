# ☕ Kafe Kita — Aplikasi Pemesanan Kafe

Aplikasi pemesanan kafe modern berbasis **Laravel 11**, **PWA**, dan **SPA** dengan desain minimalis.

## Fitur Utama

- 👤 **Registrasi Customer** — Input nama & nomor HP, data tersimpan di cache browser
- 🍽️ **Menu Interaktif** — Kategori Coffee, Non-Coffee, Makanan dengan filter real-time
- 🛒 **Keranjang** — Tambah item, atur kuantitas, catatan per item, total otomatis
- 🔔 **Notifikasi Barista** — Sound notification saat pesanan masuk (seperti WhatsApp)
- 💬 **Live Chat** — Customer dan barista bisa kirim pesan langsung per pesanan
- ✏️ **Edit Pesanan** — Barista atau customer bisa mengubah pesanan
- 📊 **Dashboard Barista** — Kelola semua pesanan aktif dengan filter status
- 🔘 **Manajemen Menu** — Toggle ketersediaan menu dari sisi barista
- 💳 **QRIS Payment** — Generate QR code QRIS dengan nominal otomatis (EMVCo Tag 54 + CRC-16)
- ✅ **Alur Selesai** — Barista tandai pesanan selesai dengan 1 tombol
- 📱 **PWA** — Install sebagai aplikasi di smartphone (offline support via Service Worker)

## Screenshot

| Halaman Registrasi | Menu & Keranjang |
|---|---|
| ![Welcome](https://github.com/user-attachments/assets/240ec45e-70e7-49e4-8bcb-9b1d9d4eae66) | ![Cart](https://github.com/user-attachments/assets/632da731-b2c0-40e6-99bc-d2967bbb0549) |

| Detail Pesanan | Dashboard Barista |
|---|---|
| ![Order](https://github.com/user-attachments/assets/b0499c5c-0f6a-4e5c-970b-16c41dcd680c) | ![Barista](https://github.com/user-attachments/assets/ee491ef4-534b-407f-8cab-ecf61860e33f) |

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.3), SQLite
- **Frontend**: Alpine.js, Tailwind CSS, Vite
- **PWA**: Service Worker, Web App Manifest
- **Real-time**: HTTP Polling (5 detik), Laravel Reverb ready
- **Payment**: QRIS EMVCo string manipulation (Tag 54 + CRC-16/CCITT-FALSE)
- **Notifikasi**: Web Audio API (sound), Web Push API ready

## Alur Aplikasi

```
Customer                          Barista
  │                                  │
  ├─ Input Nama & HP ─────────────── │
  ├─ Pilih Menu + Catatan            │
  ├─ Kirim Pesanan ──────────────────┤
  │                                  ├─ Terima Notif (suara)
  │                                  ├─ Tinjau Pesanan
  │    ◄─── Chat real-time ──────────┤
  │                                  ├─ Edit/Konfirmasi Pesanan
  ├─ Terima QR QRIS ◄────────────────┤ (Generate QRIS + Nominal)
  ├─ Bayar via Bank/E-Wallet         │
  │                                  ├─ Tandai Siap Diambil
  ├─ Ambil Pesanan ◄─────────────────┤ Pesanan Selesai
```

## Instalasi

```bash
git clone https://github.com/gunturborneo31/ordercoffee.git
cd ordercoffee

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Build assets (opsional jika sudah ada public/build)
npm run build

# Jalankan server
php artisan serve
```

Buka `http://localhost:8000` di browser.

## QRIS Setup

Tempel **QRIS static string** kamu di halaman "Konfirmasi Pesanan" di sisi barista. Sistem akan otomatis:
1. Mengubah mode dari statis (01) ke dinamis (12)
2. Menyisipkan Tag 54 (Transaction Amount) dengan nominal yang tepat
3. Menghitung ulang CRC-16/CCITT-FALSE

## Struktur Proyek

```
app/
├── Http/Controllers/
│   ├── CustomerController.php   # Registrasi & lookup customer
│   ├── MenuController.php       # Menu & ketersediaan
│   ├── OrderController.php      # CRUD pesanan & update status
│   ├── BaristaController.php    # Dashboard barista
│   └── MessageController.php   # Chat per pesanan
├── Models/                      # Customer, Menu, Order, OrderItem, Message
└── Services/
    └── QrisService.php          # EMVCo QRIS string manipulation

resources/views/
├── welcome.blade.php            # Halaman registrasi
├── menu.blade.php               # Halaman menu + keranjang
├── order.blade.php              # Detail pesanan + chat
└── barista/
    ├── dashboard.blade.php      # Dashboard barista
    └── order.blade.php          # Kelola pesanan + chat

public/
├── manifest.json                # PWA manifest
└── sw.js                        # Service Worker
```
