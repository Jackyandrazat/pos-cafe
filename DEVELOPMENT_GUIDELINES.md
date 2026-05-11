# Development Guidelines - POS Café System

**Versi**: 1.0 | **Berlaku untuk**: Semua Developer

---

## 📌 Daftar Isi

1. [Prinsip Pengembangan](#prinsip-pengembangan)
2. [Struktur Kode & Konvensi Penamaan](#struktur-kode--konvensi-penamaan)
3. [Workflow Development](#workflow-development)
4. [Database & Migration](#database--migration)
5. [Testing](#testing)
6. [API Development](#api-development)
7. [Filament Admin Development](#filament-admin-development)
8. [Code Review Checklist](#code-review-checklist)

---

## Prinsip Pengembangan

### 1. DRY (Don't Repeat Yourself)
- Hindari duplicate code dengan extract ke helper/service
- Gunakan trait untuk functionality yang dipakai multiple models
- Buat reusable methods di Service class

### 2. SOLID Principles
- **S**ingle Responsibility: Setiap class memiliki 1 tanggung jawab
- **O**pen/Closed: Open untuk extension, closed untuk modification
- **L**iskov Substitution: Child class bisa replace parent
- **I**nterface Segregation: Interface spesifik vs general
- **D**ependency Inversion: Depend pada abstraction, bukan concrete

### 3. KISS (Keep It Simple, Stupid)
- Hindari over-engineering
- Code harus readable dan maintainable
- Prefer clarity over cleverness

### 4. Fail Fast
- Validate input di awal method
- Throw exception untuk invalid state
- Jangan silent fail

### 5. Security First
- Always sanitize user input
- Use parameterized queries (Eloquent ORM)
- Implement authorization policies
- Hash sensitive data
- Validate pada server-side (bukan hanya client)

---

## Struktur Kode & Konvensi Penamaan

### File & Folder Structure

#### app/Services/
```
Services/
├── Orders/
│   ├── OrderService.php          # Order creation, update, status management
│   └── OrderValidationService.php
├── Payments/
│   ├── PaymentService.php
│   ├── PaymentGatewayManager.php
│   └── Gateways/
│       ├── QrisGateway.php
│       └── CashGateway.php
├── Inventory/
│   ├── StockService.php
│   ├── IngredientService.php
│   └── InventoryWasteReportService.php
├── Loyalty/
│   ├── LoyaltyService.php
│   ├── GiftCardService.php
│   └── LoyaltyPointService.php
├── Reports/
│   ├── SalesReportService.php
│   └── CashierReportService.php
└── Promotions/
    └── PromotionService.php
```

#### app/Filament/
```
Filament/
├── Resources/           # CRUD Resources (auto-generated tables)
│   ├── ProductResource.php
│   ├── OrderResource.php
│   └── ...
├── Pages/              # Custom pages (KDS, Reports, etc)
│   ├── Dashboard.php
│   ├── KitchenDisplay.php
│   └── SalesReport.php
└── Widgets/            # Dashboard widgets
    ├── SalesChart.php
    ├── TopProductsWidget.php
    └── ...
```

#### app/Http/
```
Http/
├── Controllers/
│   ├── Api/
│   │   ├── AuthController.php
│   │   ├── MenuController.php
│   │   ├── OrderController.php
│   │   └── PaymentController.php
│   └── Web/
│       └── LanguageSwitcherController.php
├── Requests/           # Form validation
│   ├── StoreOrderRequest.php
│   ├── StorePaymentRequest.php
│   └── ...
└── Resources/          # API Response formatting
    ├── OrderResource.php
    ├── PaymentResource.php
    └── ...
```

### Naming Conventions

#### PHP Classes
```php
// Model - singular, PascalCase
class Order
class Product
class OrderItem

// Service - PascalCase, Service suffix
class OrderService
class InventoryWasteReportService

// Request - PascalCase, Request suffix
class StoreOrderRequest
class UpdateProductRequest

// Resource - PascalCase, Resource suffix
class OrderResource
class PaymentResource

// Controller - PascalCase, Controller suffix
class OrderController
class PaymentController

// Enum - PascalCase, Singular if possible
enum OrderStatus
enum PaymentMethod

// Trait - PascalCase, mengindikasikan behavior
trait HasTimestamps
trait SoftDeletes
```

#### Database
```sql
-- Tables - snake_case, plural
CREATE TABLE orders
CREATE TABLE order_items
CREATE TABLE products

-- Columns - snake_case
user_id, created_at, is_active

-- Foreign keys - singular_id pattern
user_id -> references users(id)
order_id -> references orders(id)

-- Indexes
ALTER TABLE orders ADD INDEX idx_user_id(user_id);
ALTER TABLE orders ADD INDEX idx_created_at(created_at);
```

#### Methods & Properties
```php
// Method - camelCase, verb-based
public function calculateTotalOrder()
public function validateStockAvailability()
public function deductIngredientStock()

// Property - camelCase
private $totalAmount;
protected $orderItems;

// Boolean properties/methods - prefix with "is", "has", "can"
public function isActive()
public function hasPermission()
public function canCheckout()

// Constant - UPPER_SNAKE_CASE
const MAX_ORDER_ITEMS = 100;
const DEFAULT_CURRENCY = 'IDR';
```

#### Variables
```php
// Descriptive names
$totalOrderAmount = 0;  // ✅ Good
$tot = 0;               // ❌ Bad

$userOrders = $user->orders;      // ✅ Good
$orders = $user->orders;          // ✅ Good
$u_orders = $user->orders;        // ❌ Bad

// Avoid single letter vars (except loops)
foreach ($orders as $order) { }    // ✅ OK
for ($i = 0; $i < 10; $i++) { }   // ✅ OK
```

### Code Style

#### PSR-12 Compliance
```php
// Indentation: 4 spaces
// Line length: max 120 characters
// Use strict types

<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;

class OrderService
{
    public function __construct(
        private Order $orderModel
    ) {
    }

    /**
     * Calculate order total with tax & service fee
     * 
     * @param Order $order
     * @param float $taxRate
     * @return float
     */
    public function calculateTotal(Order $order, float $taxRate = 0.1): float
    {
        $subtotal = $order->items->sum(function ($item) {
            return $item->qty * $item->price;
        });

        $tax = $subtotal * $taxRate;
        $serviceFee = $subtotal * 0.05;

        return $subtotal + $tax + $serviceFee;
    }
}
```

#### DocBlocks
```php
/**
 * Proses pembayaran order
 *
 * Memvalidasi jumlah pembayaran, mencatat transaksi di database,
 * dan update status order menjadi completed jika lunas.
 *
 * @param Order $order Order yang akan dibayar
 * @param float $amount Jumlah pembayaran
 * @param string $method Metode pembayaran (cash|transfer|qris)
 * @return Payment Objek payment yang dibuat
 * @throws PaymentException Jika pembayaran gagal
 * @see Payment Model untuk detail struktur
 */
public function processPayment(Order $order, float $amount, string $method): Payment
{
    // Implementation
}
```

---

## Workflow Development

### 1. Feature Branch Workflow

#### Step 1: Buat Feature Branch
```bash
# Format: feature/deskripsi-singkat atau fix/deskripsi-singkat
git checkout -b feature/add-qris-payment
git checkout -b fix/order-stock-validation
```

#### Step 2: Develop & Commit
```bash
# Develop fitur di branch
# Commit dengan pesan deskriptif

git add .
git commit -m "feat: implement QRIS payment gateway integration

- Add QrisGateway class untuk handle QRIS charges
- Update Payment model dengan provider & external_reference
- Add PaymentGatewayManager untuk gateway abstraction
- Implement API endpoint untuk record QRIS payment"
```

**Commit Message Format:**
```
<type>: <subject>

<body>

<footer>
```

**Type**: feat, fix, refactor, docs, test, chore

#### Step 3: Testing
```bash
# Run unit tests
php artisan test tests/Unit/Services/PaymentServiceTest.php

# Run feature tests
php artisan test tests/Feature/Api/PaymentGatewayTest.php

# Run all tests
php artisan test
```

#### Step 4: Code Style
```bash
# Format code dengan PSR-12
php vendor/bin/pint
```

#### Step 5: Push & Create PR
```bash
git push origin feature/add-qris-payment
# Buka PR di GitHub/GitLab
```

### 2. Code Review Process

**Reviewer checklist:**
- [ ] Code follows PSR-12 style
- [ ] Tests written & passing
- [ ] Documentation updated
- [ ] No security vulnerabilities
- [ ] Database migration aman (can rollback)
- [ ] Performance acceptable
- [ ] Error handling proper
- [ ] Type hints complete

**Author checklist sebelum request review:**
- [ ] Lokal tests passed (`php artisan test`)
- [ ] Code formatted (`php vendor/bin/pint`)
- [ ] Database migrations reviewed
- [ ] Documentation updated
- [ ] Feature toggle added (jika fitur optional)
- [ ] Backward compatibility checked

### 3. Deployment Process

```bash
# 1. Merge ke main/develop
git checkout main
git merge --squash feature/add-qris-payment
git push origin main

# 2. Tag release
git tag -a v1.5.0 -m "Release v1.5.0 - QRIS payment support"
git push origin v1.5.0

# 3. Production deployment
# - Run migrations: php artisan migrate
# - Clear cache: php artisan cache:clear
# - Rebuild assets: npm run build
```

---

## Database & Migration

### Migration Conventions

#### Create Migration
```bash
# New table
php artisan make:migration create_promotions_table

# Modify table
php artisan make:migration add_provider_to_payments_table

# Drop table
php artisan make:migration drop_old_logs_table
```

#### Migration Template
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('created_by')->constrained('users');
            
            // Basic columns
            $table->string('code')->unique();
            $table->string('description')->nullable();
            
            // Enums
            $table->enum('type', ['percent', 'amount'])->default('percent');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            
            // Numeric with precision
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_subtotal', 10, 2)->default(0);
            
            // Integer
            $table->integer('quota_per_user')->default(-1); // -1 = unlimited
            $table->integer('total_quota')->default(-1);
            
            // JSON
            $table->json('schedule_days')->nullable();
            $table->time('schedule_start_time')->nullable();
            $table->time('schedule_end_time')->nullable();
            
            // Dates
            $table->date('start_date');
            $table->date('end_date');
            
            // Boolean
            $table->boolean('is_active')->default(true);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
```

### Migration Best Practices

1. **Rollback-safe**: Pastikan `down()` method bisa rollback dengan aman
2. **Data safety**: Backup data sebelum destructive migrations
3. **Indexing**: Add indexes untuk foreign keys & frequently queried columns
4. **Naming**: Migration nama harus deskriptif
5. **Transactions**: Wrap migrations yang kompleks dalam transaction

### Seeder Guidelines

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Buat kategori
        $coffee = Category::create([
            'name' => 'Kopi',
            'description' => 'Minuman kopi berbagai varian',
            'status_enabled' => true,
        ]);

        // Buat produk
        Product::factory()
            ->count(10)
            ->create([
                'category_id' => $coffee->id,
            ]);
    }
}
```

---

## Testing

### Test Structure

```php
<?php

namespace Tests\Feature\Services;

use App\Models\Order;
use App\Models\Product;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(PaymentService::class);
    }

    /** @test */
    public function it_processes_cash_payment_successfully(): void
    {
        // Arrange (Setup)
        $order = Order::factory()->create(['total_order' => 50000]);

        // Act (Execute)
        $payment = $this->paymentService->processPayment(
            order: $order,
            amount: 50000,
            method: 'cash'
        );

        // Assert (Verify)
        $this->assertEquals(50000, $payment->amount_paid);
        $this->assertEquals('captured', $payment->status);
        $this->assertEquals('completed', $order->refresh()->status);
    }

    /** @test */
    public function it_throws_exception_for_insufficient_payment(): void
    {
        // Arrange
        $order = Order::factory()->create(['total_order' => 50000]);

        // Act & Assert
        $this->expectException(PaymentException::class);

        $this->paymentService->processPayment(
            order: $order,
            amount: 30000,  // Kurang dari total
            method: 'cash'
        );
    }
}
```

### Test Coverage Guidelines

- **Models**: Relationships, scopes, accessors
- **Services**: Business logic, edge cases, error handling
- **API Endpoints**: Happy path, validation errors, auth
- **Policy**: Authorization checks per role
- **Observers**: Model events handling

### Running Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/Services/OrderServiceTest.php

# Specific test method
php artisan test tests/Feature/Services/OrderServiceTest.php --filter=it_validates_stock

# With coverage
php artisan test --coverage --coverage-html=coverage

# Stop on first failure
php artisan test --stop-on-failure

# Parallel execution
php artisan test --parallel
```

---

## API Development

### Request Validation

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorize user
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'table_id' => ['nullable', 'exists:cafe_tables,id'],
            'order_type' => ['required', 'in:dine_in,take_away,delivery'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.toppings' => ['nullable', 'array'],
            'items.*.toppings.*.topping_id' => ['exists:toppings,id'],
            
            'service_fee_order' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Minimal ada 1 item dalam order',
            'items.min' => 'Minimal ada 1 item dalam order',
            'items.*.product_id.exists' => 'Produk tidak ditemukan',
            'items.*.qty.min' => 'Jumlah minimal 1',
        ];
    }
}
```

### Response Format

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    public function store(StoreOrderRequest $request): OrderResource
    {
        $order = $this->orderService->createOrder(
            user: $request->user(),
            data: $request->validated()
        );

        return OrderResource::make($order);
    }

    public function show(Order $order): OrderResource
    {
        $this->authorize('view', $order);
        return OrderResource::make($order);
    }

    public function destroy(Order $order): Response
    {
        $this->authorize('delete', $order);
        $order->delete();

        return response()->noContent();
    }
}
```

### API Resource Class

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'table_id' => $this->table_id,
            'order_type' => $this->order_type,
            'status' => $this->status,
            
            'subtotal_order' => [
                'amount' => $this->subtotal_order,
                'currency' => 'IDR',
                'formatted' => format_currency($this->subtotal_order),
            ],
            
            'total_order' => [
                'amount' => $this->total_order,
                'currency' => 'IDR',
                'formatted' => format_currency($this->total_order),
            ],
            
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### Error Response Format

```php
// app/Exceptions/Handler.php

public function render($request, Exception $exception)
{
    if ($request->wantsJson()) {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'message' => $exception->getMessage(),
        ], $exception->getCode() ?: 500);
    }

    return parent::render($request, $exception);
}
```

---

## Filament Admin Development

### Creating Resource

```bash
php artisan make:filament-resource Order
```

### Resource Structure

```php
<?php

namespace App\Filament\Resources;

use App\Models\Order;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Penjualan';

    public static function canViewAny(): bool
    {
        // Check feature toggle & permission
        return auth()->user()->can('view-orders') && Feature::enabled('orders');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->required(),
                
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID')
                    ->searchable(),
                
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),
                
                TextColumn::make('total_order')
                    ->label('Total')
                    ->formatStateUsing(fn($state) => format_currency($state)),
            ])
            ->filters([
                // Add filters
            ])
            ->actions([
                // Add actions
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Add bulk actions
                ]),
            ]);
    }
}
```

### Feature Toggle Integration

```php
// Disable navigation jika fitur OFF
public static function shouldRegisterNavigation(): bool
{
    return Feature::enabled('orders') && auth()->user()->can('view-orders');
}

// Disable form access
public function mount(): void
{
    if (!Feature::enabled('orders')) {
        abort(403, 'Feature tidak aktif');
    }
}

// Conditional columns/fields
->visible(fn () => Feature::enabled('promotions'))
```

---

## Code Review Checklist

### Pre-Commit Checklist

- [ ] Code tested (`php artisan test`)
- [ ] Code formatted (`php vendor/bin/pint`)
- [ ] Type hints complete
- [ ] No `dd()` atau `var_dump()`
- [ ] No hardcoded values (use config/constants)
- [ ] Proper error handling
- [ ] Documentation updated

### PR Review Checklist

#### Code Quality
- [ ] Follows PSR-12 standard
- [ ] No unnecessary complexity
- [ ] DRY principles applied
- [ ] Security best practices followed

#### Testing
- [ ] Tests written for new code
- [ ] All tests passing
- [ ] Edge cases covered
- [ ] Test coverage > 80%

#### Documentation
- [ ] README updated
- [ ] Code comments clear
- [ ] API docs updated
- [ ] Changelog updated

#### Database
- [ ] Migrations reversible
- [ ] Data safe
- [ ] Indexes added for FK
- [ ] No N+1 queries

#### Security
- [ ] Input validated
- [ ] Authorization checked
- [ ] No sensitive data logged
- [ ] SQL injection safe (using ORM)

#### Performance
- [ ] Queries optimized
- [ ] Caching implemented (if needed)
- [ ] No memory leaks
- [ ] Response time acceptable

---

## Tools & Commands

### Daily Development Commands

```bash
# Start development
php artisan serve --host=0.0.0.0 --port=8000
npm run dev

# Run tests
php artisan test

# Format code
php vendor/bin/pint

# Database commands
php artisan migrate
php artisan migrate:rollback
php artisan db:seed

# Cache management
php artisan cache:clear
php artisan view:cache
php artisan config:cache

# Feature toggle
php artisan feature:toggle kitchen_display --enable
php artisan feature:toggle kitchen_display --disable

# Generate code
php artisan make:model ModelName -mfs  # Model, Migration, Factory, Seeder
php artisan make:service ServiceName
php artisan make:request FormRequestName
php artisan make:filament-resource ResourceName
```

### Useful Artisan Commands

```bash
# Database inspection
php artisan db:show               # Show database info
php artisan db:table users        # Show table structure

# Debug
php artisan tinker               # Interactive shell
php artisan route:list           # Show all routes
php artisan model:show User      # Show model info

# Cache & queue
php artisan queue:work           # Start queue worker
php artisan schedule:run         # Run scheduled tasks
```

---

## References

- **Laravel Documentation**: https://laravel.com/docs/11
- **Filament Documentation**: https://filamentphp.com
- **PSR-12 Style Guide**: https://www.php-fig.org/psr/psr-12/
- **API Design Guide**: https://restfulapi.net

---

**Last Updated**: Mei 2026 | Dokumentasi v1.0
