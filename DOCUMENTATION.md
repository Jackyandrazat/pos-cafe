# POS Café System - Dokumentasi Lengkap

**Versi**: 1.0.0 | **Terakhir Diupdate**: Mei 2026

---

## 📑 Daftar Isi

1. [Ringkasan Proyek](#ringkasan-proyek)
2. [Teknologi & Stack](#teknologi--stack)
3. [Arsitektur Sistem](#arsitektur-sistem)
4. [Fitur Utama & Status](#fitur-utama--status)
5. [Struktur Database](#struktur-database)
6. [Instalasi & Setup](#instalasi--setup)
7. [API Endpoints](#api-endpoints)
8. [Alur Pengguna Utama](#alur-pengguna-utama)

---

## Ringkasan Proyek

### Visi & Tujuan

**POS Café System** adalah platform point-of-sale (POS) terintegrasi yang dirancang khusus untuk manajemen operasional kafe modern. Sistem ini menghubungkan berbagai aspek bisnis kafe mulai dari katalog menu, inventory management, transaksi penjualan, hingga pelaporan komprehensif.

### Tujuan Utama

- ✅ Mempercepat proses transaksi di kafe
- ✅ Menjaga konsistensi data inventori dan penjualan
- ✅ Memberikan visibilitas penuh ke management tentang performa bisnis
- ✅ Meningkatkan akuntabilitas kasir melalui audit trail lengkap
- ✅ Mendukung program loyalitas dan promosi dinamis
- ✅ Integrasi pembayaran digital (QRIS, e-wallet)

### Target Pengguna

| Persona | Peran | Hak Akses |
|---------|------|----------|
| **Owner** | Monitoring bisnis & approval strategis | Akses penuh ke semua modul + laporan global |
| **Admin** | Manajemen katalog, inventory, setup akun | Semua modul kecuali approval owner-only |
| **Kasir** | Input order & pembayaran, laporan pribadi | Order, payment, shift, rekap data pribadi |
| **Inventory/Dapur** | Update stok & monitoring produksi | Inventory, pembelian, KDS (Kitchen Display) |

---

## Teknologi & Stack

### Backend
- **Framework**: Laravel 11 (PHP 8.2+)
- **Authentication**: Laravel Sanctum (Token-based API)
- **Database**: MySQL 8.0+
- **Admin Panel**: Filament v3.3
- **PDF Generation**: DOMPDF

### Frontend
- **Build Tool**: Vite
- **Charting**: Chart.js 4.4.9
- **HTTP Client**: Axios
- **Templating**: Blade (Laravel)

### Development & Testing
- **Package Manager**: Composer
- **Testing**: PHPUnit 10.5
- **Linting**: Laravel Pint
- **Development Server**: Laravel Artisan

### Key Dependencies
```json
{
  "laravel/framework": "^11.0",
  "laravel/sanctum": "^4.2",
  "filament/filament": "^3.3",
  "barryvdh/laravel-dompdf": "^3.1"
}
```

---

## Arsitektur Sistem

### Struktur Folder Utama

```
pos-cafe/
├── app/
│   ├── Filament/              # Admin Panel & Resource
│   │   ├── Resources/         # CRUD Resources
│   │   ├── Pages/             # Custom Pages (Dashboard, KDS, Reports)
│   │   └── Widgets/           # Dashboard Widgets
│   ├── Http/
│   │   ├── Controllers/       # API & Web Controllers
│   │   ├── Requests/          # Form Requests & Validation
│   │   ├── Resources/         # API Resource Classes
│   │   └── Middleware/        # Custom Middleware
│   ├── Models/                # Eloquent Models
│   ├── Services/              # Business Logic
│   │   ├── Payments/          # Payment Gateway Integration
│   │   ├── Loyalty/           # Loyalty Program Service
│   │   └── Reports/           # Reporting Service
│   ├── Enums/                 # Enum Values (OrderStatus, etc)
│   ├── Observers/             # Model Observers
│   ├── Policies/              # Authorization Policies
│   ├── Exceptions/            # Custom Exceptions
│   └── Support/               # Helpers & Utilities
├── database/
│   ├── migrations/            # Schema Migrations
│   ├── seeders/               # Database Seeders
│   └── factories/             # Model Factories for Testing
├── routes/
│   ├── api.php                # API Routes (v1)
│   ├── web.php                # Web Routes
│   └── console.php            # Artisan Commands
├── config/
│   ├── features.php           # Feature Toggle Configuration
│   └── [other configs]
├── resources/
│   ├── views/                 # Blade Templates
│   ├── js/                    # JavaScript Files
│   └── css/                   # Stylesheets
├── tests/
│   ├── Feature/               # Feature Tests
│   └── Unit/                  # Unit Tests
└── storage/
    ├── logs/                  # Application Logs
    └── app/                   # Temporary Files
```

### Alur Data Utama

```
┌─────────────────┐
│   Client/API    │
└────────┬────────┘
         │
    ┌────▼─────────────────────────┐
    │   HTTP Request (Sanctum)     │
    │   /api/v1/orders             │
    └────┬──────────────────────────┘
         │
    ┌────▼─────────────────────────┐
    │   Route (routes/api.php)      │
    │   Resolve Controller          │
    └────┬──────────────────────────┘
         │
    ┌────▼─────────────────────────┐
    │   API Controller             │
    │   Validate via FormRequest   │
    │   Call Service               │
    └────┬──────────────────────────┘
         │
    ┌────▼─────────────────────────┐
    │   Service Layer             │
    │   - Business Logic          │
    │   - Transactions            │
    │   - Model Interactions      │
    └────┬──────────────────────────┘
         │
    ┌────▼─────────────────────────┐
    │   Models + Database         │
    │   - Eloquent Relationships  │
    │   - Model Observers         │
    │   - Query Execution         │
    └────┬──────────────────────────┘
         │
    ┌────▼─────────────────────────┐
    │   JSON Response             │
    │   (API Resource Class)      │
    └────┬──────────────────────────┘
         │
    ┌────▼─────────────────────────┐
    │   Client Receives Data      │
    └─────────────────────────────┘
```

### Design Pattern yang Digunakan

1. **Service Layer Pattern**: Bisnis logic terpusat di `app/Services/`
2. **Repository Pattern**: Akses data melalui model Eloquent
3. **Observer Pattern**: Model observers untuk event handling (stock deduction, loyalty points)
4. **Resource Pattern**: API response transformation via Resource classes
5. **Feature Toggle Pattern**: Modul dapat diaktifkan/dimatikan via config
6. **Policy-based Authorization**: Filament resources menggunakan policies untuk kontrol akses

---

## Fitur Utama & Status

### ✅ Fitur Selesai (Production Ready)

#### 1. **Manajemen Katalog Produk**
- ✅ CRUD Produk & Kategori
- ✅ Pengelolaan Harga & Stock
- ✅ Multiple SKU & Deskripsi Detail
- ✅ Status Availability Toggle
- **Lokasi**: `Filament Resources > Products & Categories`

#### 2. **Manajemen Meja & Area**
- ✅ CRUD Area & Table Numbers
- ✅ Table Status Management (Available, Reserved, Occupied)
- ✅ Optional untuk setiap order
- **Lokasi**: `Filament Resources > Café Tables`

#### 3. **Sistem Order & Order Items**
- ✅ Order Creation dengan inline items builder
- ✅ Multiple items per order dengan qty & price
- ✅ Topping support dengan harga terpisah
- ✅ Discount per item & per order
- ✅ Service fee calculation
- ✅ Order type (Dine-in, Take-away, Delivery)
- ✅ Stock validation sebelum order disimpan
- **Lokasi**: `Filament Resources > Orders`

#### 4. **Sistem Pembayaran**
- ✅ Multi-payment method (Cash, Transfer, QRIS, E-wallet)
- ✅ Change calculation otomatis
- ✅ Payment status tracking (Pending, Captured, Failed)
- ✅ Order status auto-update saat payment lunas
- ✅ Shift linkage untuk setiap payment
- **Lokasi**: `Filament Resources > Payments`

#### 5. **Shift Management**
- ✅ Open/Close Shift dengan saldo awal
- ✅ Automatic closing balance & total sales calculation
- ✅ Shift-based payment tracking
- ✅ Notes & audit trail
- **Lokasi**: `Filament Resources > Shifts`

#### 6. **Inventory & Ingredient Management**
- ✅ CRUD Ingredients dengan stock tracking
- ✅ Automatic stock deduction berdasarkan product composition
- ✅ Expired date monitoring
- ✅ Purchase order management
- ✅ Purchase items dengan unit pricing
- **Lokasi**: `Filament Resources > Ingredients`

#### 7. **Ingredient Waste Tracking**
- ✅ Log waste dengan reason & amount
- ✅ Automatic stock adjustment
- ✅ Waste report dengan KPI (cost, percentage)
- ✅ Filter by date range & ingredient
- ✅ Export CSV support
- **Lokasi**: `Filament Pages > Inventory Waste Dashboard`

#### 8. **Loyalitas & Customer Management**
- ✅ Customer database dengan contact info
- ✅ Automatic loyalty points calculation (1% default)
- ✅ Customer lifetime value tracking
- ✅ Point transaction history
- ✅ Customer preferences tagging
- **Lokasi**: `Filament Resources > Customers`

#### 9. **Order Status Tracking**
- ✅ Status enum: Draft, Pending, Confirmed, Preparing, Ready, Completed
- ✅ Status log history untuk audit trail
- ✅ Manual status update
- **Lokasi**: `Filament Order Detail`

#### 10. **Kitchen Display System (KDS)**
- ✅ Real-time order queue display
- ✅ Status update buttons (Start, Ready, Complete)
- ✅ Auto-refresh setiap 15 detik
- ✅ Filter by status
- ✅ Order detail dengan item & notes
- **Lokasi**: `Filament Pages > Kitchen Display`
- **Feature Toggle**: `kitchen_display`

#### 11. **Promosi & Dynamic Pricing**
- ✅ Promotion codes dengan diskon %/amount
- ✅ Minimum subtotal validation
- ✅ Kuota per user tracking
- ✅ Schedule-based promotions (days + time window)
- ✅ Overnight support untuk jadwal
- ✅ Promo usage history
- **Lokasi**: `Filament Resources > Promotions`
- **Feature Toggle**: `promotions`

#### 12. **Gift Cards**
- ✅ Create, issue, redeem gift cards
- ✅ Balance tracking & transaction history
- ✅ Expiration date management
- ✅ Batch import untuk bulk issuance
- **Lokasi**: `Filament Resources > Gift Cards`
- **Feature Toggle**: `gift_cards`

#### 13. **Loyalty Challenges**
- ✅ Create time-based challenges untuk customers
- ✅ Progress tracking & completion rewards
- ✅ Multiple awards per challenge
- ✅ Automatic point awarding saat complete
- **Lokasi**: `Filament Resources > Loyalty Challenges`
- **Feature Toggle**: `loyalty_challenges`

#### 14. **Dashboard & Reporting**
- ✅ Sales dashboard dengan real-time charts
- ✅ Top selling products & items
- ✅ Sales by period (Daily, Weekly, Monthly)
- ✅ Expired ingredients widget
- ✅ Detailed transaction report
- ✅ Per-cashier performance recap
- ✅ Export CSV dengan chunking untuk memory efficiency
- **Lokasi**: `Filament Dashboard & Pages > Sales Report`

#### 15. **API Self-Order (Web-based Ordering)**
- ✅ Public menu endpoint (`GET /api/v1/menus`)
- ✅ Guest authentication flow (`POST /api/v1/auth/guest`)
- ✅ Authenticated order creation & management
- ✅ Order status tracking via API
- ✅ Payment recording & status update
- ✅ Pagination & filtering support
- **Dokumentasi**: [api-implementation.md](api-implementation.md)

#### 16. **Role & Permission System**
- ✅ Role-based access control (Owner, Admin, Kasir, Dapur)
- ✅ Granular resource permissions
- ✅ Policy-based authorization
- ✅ Filament resource visibility control
- **Lokasi**: `Filament Resources > Roles`

#### 17. **Multi-language Support**
- ✅ Language switcher middleware
- ✅ Session-based locale preference
- ✅ Translation files untuk UI
- ✅ Date & currency formatting
- **Lokasi**: `Livewire > LanguageSwitcher`

#### 18. **Feature Toggle System**
- ✅ Configurable module activation
- ✅ Database-backed feature flags
- ✅ CLI toggle command
- ✅ Filament admin UI untuk toggle management
- **Konfigurasi**: `config/features.php`
- **Dokumentasi**: [feature-toggle.md](docs/feature-toggle.md)

#### 19. **WhatsApp Receipt Integration**
- ✅ Send order receipt via WhatsApp
- ✅ Include order details & items
- ✅ Dynamic message formatting
- **Implementasi**: Service layer dengan WhatsApp API

#### 20. **Table Queue Management**
- ✅ Queue entry tracking untuk table waiting
- ✅ Estimated wait time calculation
- ✅ Auto-notification saat table siap
- **Feature Toggle**: `table_management`

---

## Struktur Database

### Tabel Utama & Relasi

```
┌──────────────────────────────────────────────────────────┐
│                   USERS & SECURITY                       │
├──────────────────────────────────────────────────────────┤
users
  ├─ id, name, email, password
  ├─ is_active, is_guest
  ├─ timestamps, soft_deletes
  └─ Relations: hasMany(orders), hasMany(shifts), hasMany(purchases)

roles (Owner, Admin, Kasir, Dapur)
  └─ pivot: role_user

feature_flags (Modul activation)
  └─ key, is_enabled, updated_at
```

```
┌──────────────────────────────────────────────────────────┐
│               CATALOG & INVENTORY                        │
├──────────────────────────────────────────────────────────┤
categories
  ├─ id, name, description, status_enabled
  └─ hasMany(products)

products
  ├─ id, category_id, name, sku, price, cost_price, stock_qty
  ├─ description, status_enabled, timestamps
  └─ Relations: hasMany(orderItems), hasMany(productIngredients)

toppings
  ├─ id, name, price, is_active
  └─ hasMany(orderItemToppings) through orderItems

ingredients
  ├─ id, name, stock_qty, unit, price_per_unit, expired
  ├─ timestamps, soft_deletes
  └─ Relations: hasMany(productIngredients), hasMany(purchaseItems)

product_ingredients
  ├─ product_id, ingredient_id, quantity_used, unit
  └─ Untuk automatic stock deduction saat order

product_sizes (optional)
  ├─ product_id, size, price_modifier

ingredient_wastes
  ├─ id, ingredient_id, quantity, reason, shift_id, user_id
  ├─ notes, created_at
  └─ Observer: auto adjust ingredient stock
```

```
┌──────────────────────────────────────────────────────────┐
│              LOCATION & SEATING                          │
├──────────────────────────────────────────────────────────┤
areas
  ├─ id, name, description, status_enabled
  └─ hasMany(cafeTables)

cafe_tables
  ├─ id, area_id, table_number, status (available/reserved/occupied)
  └─ hasMany(orders)

table_queue_entries
  ├─ table_id, customer_count, estimated_wait_time
  ├─ created_at, notified_at
  └─ For queue management
```

```
┌──────────────────────────────────────────────────────────┐
│            ORDERS & TRANSACTIONS                         │
├──────────────────────────────────────────────────────────┤
orders
  ├─ id, user_id, table_id, customer_id
  ├─ order_type (enum), customer_name, notes
  ├─ status (enum), promotion_id, promotion_discount
  ├─ subtotal_order, discount_order, service_fee_order, total_order
  ├─ timestamps
  └─ Relations: hasMany(orderItems), hasMany(payments), hasMany(statusLogs)

order_items
  ├─ id, order_id, product_id, qty, price, discount_amount, subtotal
  ├─ timestamps
  └─ Relations: hasMany(orderItemToppings)

order_item_toppings
  ├─ order_item_id, topping_id, price

order_status_logs
  ├─ id, order_id, status, description, created_at
  └─ Untuk audit trail & order timeline
```

```
┌──────────────────────────────────────────────────────────┐
│            PAYMENTS & SHIFTS                             │
├──────────────────────────────────────────────────────────┤
payments
  ├─ id, order_id, shift_id, user_id
  ├─ payment_method (enum), amount_paid, change_return
  ├─ provider, external_reference, status (enum)
  ├─ meta (JSON untuk QR/deeplink), paid_at
  ├─ payment_date, timestamps
  └─ Gateway integration untuk QRIS/e-wallet

shifts
  ├─ id, user_id, shift_open_time, shift_close_time
  ├─ opening_balance, closing_balance, total_sales
  ├─ notes, timestamps, soft_deletes
  └─ Relations: hasMany(payments)
```

```
┌──────────────────────────────────────────────────────────┐
│            PURCHASE & RESTOCKING                         │
├──────────────────────────────────────────────────────────┤
purchases
  ├─ id, user_id, invoice_number, purchase_date
  ├─ total_amount, notes, timestamps
  └─ hasMany(purchaseItems)

purchase_items
  ├─ id, purchase_id, ingredient_id
  ├─ quantity, unit, price_per_unit
  └─ Observer: auto adjust ingredient stock
```

```
┌──────────────────────────────────────────────────────────┐
│           LOYALITAS & PROMOTIONS                         │
├──────────────────────────────────────────────────────────┤
customers
  ├─ id, name, email, phone, preferences (JSON)
  ├─ total_points, lifetime_value, last_order_date
  ├─ timestamps, soft_deletes
  └─ hasMany(orders), hasMany(pointTransactions)

customer_point_transactions
  ├─ id, customer_id, order_id, points, transaction_type
  ├─ notes, created_at

promotions
  ├─ id, code, description, type (percent/amount)
  ├─ discount_value, min_subtotal, quota_per_user
  ├─ is_active, start_date, end_date
  ├─ schedule_days (JSON), schedule_start_time, schedule_end_time
  ├─ timestamps
  └─ hasMany(promotionUsages)

promotion_usages
  ├─ id, promotion_id, order_id, discount_applied, created_at

gift_cards
  ├─ id, code, initial_balance, current_balance
  ├─ customer_id, is_active, expires_at, timestamps

gift_card_transactions
  ├─ id, gift_card_id, type (issue/use), amount, created_at

loyalty_challenges
  ├─ id, name, description, start_date, end_date
  ├─ target_orders, target_amount, timestamps

loyalty_challenge_progress
  ├─ id, challenge_id, customer_id, current_progress, completed_at

loyalty_challenge_awards
  ├─ id, challenge_id, reward_type (points/discount)
  ├─ reward_value, timestamps
```

---

## Instalasi & Setup

### Prasyarat

- **PHP**: 8.2 atau lebih tinggi
- **MySQL**: 8.0 atau lebih tinggi (atau MariaDB 10.6+)
- **Composer**: 2.0 atau lebih tinggi
- **Node.js**: 16+ (untuk Vite)

### Langkah Instalasi

#### 1. Clone Repository
```bash
git clone <repository-url> pos-cafe
cd pos-cafe
```

#### 2. Install Dependencies
```bash
composer install
npm install
```

#### 3. Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan:
```ini
APP_NAME="POS Cafe"
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_cafe
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=localhost
```

#### 4. Database Setup
```bash
php artisan migrate
php artisan db:seed  # Optional, untuk demo data
```

#### 5. Build Assets
```bash
npm run build  # Production
npm run dev    # Development with watch
```

#### 6. Generate Storage Link
```bash
php artisan storage:link
```

#### 7. Start Development Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Akses aplikasi di: `http://localhost:8000`

### Default Admin Credentials
```
Email: admin@poscape.local
Password: password
```

*(Sesuaikan di database seeder)*

---

## API Endpoints

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication

#### Guest Login
```http
POST /api/v1/auth/guest
Content-Type: application/json

{
  "name": "Walk-in Guest",
  "phone": "+62812..."
}
```

**Response (201)**
```json
{
  "token": "1|iN9k...",
  "token_type": "Bearer",
  "user": {
    "id": 321,
    "name": "Walk-in Guest",
    "phone": "+62812...",
    "is_guest": true
  }
}
```

### Menu & Kategori

#### List Menu
```http
GET /api/v1/menus?search=kopi&category_id=1&is_available=true&per_page=20&page=1
```

**Response (200)**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Espresso",
      "category_id": 1,
      "category": {"id": 1, "name": "Kopi"},
      "price": {
        "amount": 25000,
        "currency": "IDR",
        "formatted": "Rp 25.000"
      },
      "stock_qty": 50,
      "description": "Strong black coffee"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 45
  }
}
```

#### Get Menu Detail
```http
GET /api/v1/menus/1
```

#### List Kategori
```http
GET /api/v1/categories
```

### Order Management

#### Create Order
```http
POST /api/v1/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "table_id": 5,
  "order_type": "dine_in",
  "customer_name": "John Doe",
  "items": [
    {
      "product_id": 1,
      "qty": 2,
      "discount_amount": 0,
      "toppings": [
        {"topping_id": 1, "price": 5000}
      ]
    }
  ],
  "service_fee_order": 0,
  "notes": "Extra hot"
}
```

#### Get Orders
```http
GET /api/v1/orders?status=pending&per_page=10
Authorization: Bearer {token}
```

#### Submit Order (Draft → Pending)
```http
POST /api/v1/orders/1/submit
Authorization: Bearer {token}
```

#### Cancel Order
```http
POST /api/v1/orders/1/cancel
Authorization: Bearer {token}
```

#### Add Item to Order
```http
POST /api/v1/orders/1/items
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 2,
  "qty": 1,
  "discount_amount": 0
}
```

#### Update Order Item
```http
PATCH /api/v1/orders/1/items/3
Authorization: Bearer {token}
Content-Type: application/json

{
  "qty": 3,
  "discount_amount": 5000
}
```

#### Delete Order Item
```http
DELETE /api/v1/orders/1/items/3
Authorization: Bearer {token}
```

### Pembayaran

#### Record Payment
```http
POST /api/v1/orders/1/payments
Authorization: Bearer {token}
Content-Type: application/json

{
  "payment_method": "cash",
  "amount_paid": 55000
}
```

Untuk QRIS:
```json
{
  "payment_method": "qris",
  "amount_paid": 55000
}
```

**Response (201)**
```json
{
  "id": 123,
  "order_id": 1,
  "payment_method": "cash",
  "amount_paid": 55000,
  "change_return": 0,
  "status": "captured",
  "payment_date": "2024-05-11T10:30:00+07:00"
}
```

#### Get Payment Details
```http
GET /api/v1/payments/123
Authorization: Bearer {token}
```

---

## Alur Pengguna Utama

### 🔄 Alur Kasir (Point of Sale)

```
1. LOGIN
   └─ Kasir login dengan email & password

2. OPEN SHIFT
   └─ Buka shift dengan saldo awal

3. CREATE ORDER
   └─ Select table
   └─ Add products + toppings
   └─ Set discount & notes
   └─ System validates stock

4. CONFIRM ORDER
   └─ Order status: PENDING
   └─ Order visible in Kitchen Display

5. RECORD PAYMENT
   └─ Select payment method (Cash/QRIS/Transfer/E-wallet)
   └─ Input amount paid
   └─ System calculates change
   └─ Order auto-complete

6. PRINT/SEND RECEIPT
   └─ Print to printer
   └─ Or send via WhatsApp

7. CLOSE SHIFT
   └─ Input closing balance
   └─ System auto-calculates total sales
   └─ Review shift report

8. EXIT
```

### 👨‍🍳 Alur Dapur (Kitchen Display System)

```
1. VIEW ORDER QUEUE
   └─ Kitchen Display System menampilkan order pending

2. START COOKING
   └─ Click "Mulai Masak"
   └─ Order status: PREPARING

3. MARK AS READY
   └─ Click "Tandai Siap"
   └─ Order status: READY
   └─ Kasir notified

4. COMPLETE
   └─ Auto-complete saat payment done
```

### 📊 Alur Owner (Monitoring & Reporting)

```
1. VIEW DASHBOARD
   └─ Real-time sales overview
   └─ Top selling products
   └─ Charts & KPIs

2. GENERATE REPORTS
   └─ Sales report (daily/weekly/monthly)
   └─ Per-cashier performance
   └─ Inventory report
   └─ Export to CSV

3. MANAGE INVENTORY
   └─ View expired ingredients
   └─ Create purchase orders
   └─ Monitor stock levels

4. REVIEW LOYALTY
   └─ High-value customers
   └─ Loyalty point transactions
   └─ Challenge progress

5. MANAGE PROMOTIONS
   └─ Create promo codes
   └─ Set schedule & discount
   └─ View usage statistics
```

---

## Fitur Lanjutan

Untuk informasi detail tentang fitur-fitur advanced, lihat dokumentasi khusus:

- [Feature Toggle System](docs/feature-toggle.md) - Aktivasi/deaktivasi modul
- [Kitchen Display System & Digital Payments](docs/kds-and-digital-payments.md) - KDS & QRIS
- [Dynamic Pricing & Promotions](docs/dynamic-pricing-promotions.md) - Promo dengan schedule
- [Inventory Waste & Loyalty](docs/inventory-waste-and-loyalty.md) - Waste tracking & loyalty
- [Web Self-Order API](api-implementation.md) - Self-service ordering

---

## Troubleshooting

### Masalah Umum

#### 1. Database Connection Error
```
Error: could not find driver
```
**Solusi**: Install MySQL driver untuk PHP
```bash
# Windows (via WSL/Linux)
apt-get install php8.2-mysql

# macOS (via Homebrew)
brew install php@8.2
```

#### 2. Permission Denied di Storage
```bash
chmod -R 775 storage bootstrap/cache
```

#### 3. Composer Timeout
```bash
composer install --no-interaction --no-plugins --no-scripts
```

#### 4. CORS Error pada API
Update `.env`:
```ini
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost:3000
```

---

## Support & Kontribusi

- **Issue Tracking**: GitHub Issues
- **Documentation**: Lihat folder `/docs`
- **Testing**: `php artisan test`
- **Code Standards**: `php vendor/bin/pint`

---

**Last Updated**: Mei 2026 | Dokumentasi v1.0
