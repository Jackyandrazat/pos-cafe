# Web Self-Order API – Technical Notes

## Overview
- **Stack**: Laravel 11 + Sanctum, decoupled from Filament admin.
- **Versioning**: All endpoints live under `/api/v1` via `routes/api.php`.
- **Auth**: Sanctum personal access tokens (guests or registered users). Public menu/category endpoints remain open.
- **Domain objects**: `Product` (Menu), `Category`, `Order`, `OrderItem`, `Payment`, with `OrderStatusLog` (timeline) and `OrderStatus` enum.

## Setup & Implementation Steps
1. **Clone & install**
   ```bash
   git clone <repo> && cd pos-cafe
   composer install
   npm install # optional (Filament asset build)
   ```
2. **Environment config**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   - Configure `DB_*`, `APP_URL`, `APP_TIMEZONE`, `APP_LOCALE`.
   - For Sanctum SPA usage, set `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN`.
3. **Sanctum assets & caching** (already committed but safe to rerun):
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan vendor:publish --tag=filament-config --force # optional if updating Filament assets
   ```
4. **Database migrations & seed (if any)**
   ```bash
   php artisan migrate
   php artisan db:seed # optional demo data
   ```
5. **Serve API**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
   Frontend can now call `http://<host>:8000/api/v1/...`.
6. **Generate tokens**
   - Registered user: `php artisan tinker` → `$user->createToken('api')->plainTextToken`.
   - Guest flow: call `POST /api/v1/auth/guest` to obtain a short-lived customer token.
7. **Verify**
   ```bash
   php artisan test
   ```
   Optionally create manual requests via Postman/Insomnia to ensure menus/orders/payments endpoints respond as expected.

## Authentication & Guest Flow
1. `POST /api/v1/auth/guest` – creates an `is_guest` user (random email/password) and returns a Sanctum token.
2. Authenticated requests include `Authorization: Bearer {token}`. Guard configured via `config/auth.php` (`sanctum`).

## Endpoint Matrix
| Endpoint | Method | Description | Auth |
|---|---|---|---|
| `/api/v1/store-config` | GET | Store config (self_order_allow_cash, geofence lat/lng/radius). | Public |
| `/api/v1/tables/{tableNumber}` | GET | Table detail (area, capacity, status) for QR verification. | Public |
| `/api/v1/auth/guest` | POST | Anonymous guest login (4-hr TTL session). | Public |
| `/api/v1/auth/member` | POST | Member login via phone number (returns points & tier). | Public |
| `/api/v1/auth/logout` | POST | Revoke current Sanctum token. | Sanctum |
| `/api/v1/menus` | GET | Paginated menu list (`search`, `category_id`, `is_available`, `per_page` 1–50). | Public |
| `/api/v1/menus/{id}` | GET | Menu detail inc. category, size variants & toppings. | Public |
| `/api/v1/categories` | GET | Category list with `menu_count`. | Public |
| `/api/v1/orders` | GET | Current user’s orders (optional `status`). | Sanctum |
| `/api/v1/orders` | POST | Create draft order + items (supports `X-Idempotency-Key`). | Sanctum |
| `/api/v1/orders/{order}` | GET | Order detail + items + totals snapshot. | Sanctum |
| `/api/v1/orders/{order}/submit` | POST | Draft → pending (requires ≥1 item). | Sanctum |
| `/api/v1/orders/{order}/cancel` | POST | Cancel order (auto-restores deducted ingredient stock). | Sanctum |
| `/api/v1/orders/{order}/items` | POST | Add/merge items (supports `size_id` & `toppings`). | Sanctum |
| `/api/v1/orders/{order}/items/{item}` | PATCH | Update qty/discount. | Sanctum |
| `/api/v1/orders/{order}/items/{item}` | DELETE | Remove item. | Sanctum |
| `/api/v1/orders/{order}/status` | GET | Current status + timeline history logs. | Sanctum |
| `/api/v1/orders/{order}/payments` | GET | Payments for order. | Sanctum |
| `/api/v1/orders/{order}/payments` | POST | Record payment intent (validates balance & idempotency). | Sanctum |
| `/api/v1/payments/{payment}` | GET | Payment detail (ownership enforced). | Sanctum |
| `/api/v1/payments/midtrans/notification` | POST | Midtrans webhook callback (with DB lock guard). | Public / IP |
| `/api/v1/payments/xendit/callback` | POST | Xendit webhook callback (with DB lock guard). | Public / IP |

## API Reference
### 1. Authentication
#### `POST /api/v1/auth/guest`
- **Purpose**: issues a Sanctum token for anonymous customers.
- **Request Body**
  ```json
  {
    "name": "Walk-in Guest",
    "phone": "+62812..."
  }
  ```
- **Response (201)**
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
- Use the returned token in `Authorization: Bearer <token>` for all protected endpoints.

### 2. Menu & Category
#### `GET /api/v1/menus`
- **Query Params**: `search`, `category_id`, `is_available` (bool), `per_page` (1–50), `page`.
- **Response**: paginated list (see sample earlier) containing price blocks (`amount`, `currency`, `formatted`).

#### `GET /api/v1/menus/{menuId}`
- Returns a single menu item with category info and availability flag (`is_available`).

#### `GET /api/v1/categories`
- Returns categories sorted by `name` plus `menu_count` derived from product relation.

### 3. Orders
#### `GET /api/v1/orders`
- **Headers**: `Authorization: Bearer <token>`.
- **Query Params**: `status`, `per_page`, `page`.
- **Response**: Paginated `OrderResource` collection (items + totals).

#### `POST /api/v1/orders`
- **Body** (validated by `StoreOrderRequest`):
  ```json
  {
    "order_type": "dine_in",
    "table_id": 3,
    "customer_name": "John",
    "notes": "Less sugar",
    "items": [
      {"menu_id": 10, "quantity": 2, "discount_amount": 1000}
    ]
  }
  ```
- **Rules**:
  - Menu must be available (`status_enabled = true`).
  - Discount <= unit price; subtotal recalculated.
  - Order stored as `draft`, status log entry created.

#### `GET /api/v1/orders/{order}`
- Returns a single order with items (ownership enforced).

#### `POST /api/v1/orders/{order}/submit`
- Moves `draft → pending`. Fails if order has 0 items or isn’t draft.

#### `POST /api/v1/orders/{order}/cancel`
- Cancels when status is `draft|pending|confirmed`; logs cancellation.

### 4. Order Items
#### `POST /api/v1/orders/{order}/items`
- Adds or merges an item while order is draft.
- **Body**:
  ```json
  {"menu_id": 55, "quantity": 1, "discount_amount": 0}
  ```
- Returns created/updated `OrderItemResource`.

#### `PATCH /api/v1/orders/{order}/items/{item}`
- Updates quantity/discount.

#### `DELETE /api/v1/orders/{order}/items/{item}`
- Removes an item; responds with HTTP 204 on success.

### 5. Order Status Tracking
#### `GET /api/v1/orders/{order}/status`
- Returns payload containing `current_status` (code + label + updated_at) and `history` array from `order_status_logs` ordered chronologically.
- Use for polling or bridging to WebSocket events.

### 6. Payments (Optional/Flexible)
#### `GET /api/v1/orders/{order}/payments`
- Lists prior payments in descending order of creation.

#### `POST /api/v1/orders/{order}/payments`
- **Body**:
  ```json
  {
    "payment_method": "cash", // enum: cash|qris|transfer|ewallet
    "amount": 50000
  }
  ```
- **Rules**:
  - Order must belong to caller & not be cancelled.
  - Outstanding balance computed = `grand_total - sum(payments.amount_paid)`.
  - Non-cash methods cannot exceed due amount. Cash refunds `change_return` if overpaid.
  - When balance reaches zero, order auto-updates to `completed` and logs status.

#### `GET /api/v1/payments/{payment}`
- Returns a single payment (ownership enforced by associated order).

### 7. Error Format
- Validation or business rule errors return HTTP 422 with structure:
  ```json
  {
    "message": "Only draft orders can be submitted.",
    "errors": {
      "order": ["Only draft orders can be submitted."]
    }
  }
  ```
- Forbidden access returns 403 (`{"message":"You do not have access to this order."}`).
- Missing resources return 404.

## Validation & Business Rules
- All write endpoints use dedicated Form Requests (`StoreOrderRequest`, `Store/UpdateOrderItemRequest`, `StorePaymentRequest`).
- Product availability enforced (only `status_enabled` menus allowed).
- Discounts capped at unit price; subtotal recalculated after every mutation.
- Submit requires draft status + at least one item; cancel allowed until `preparing`.
- Payment creation declines for cancelled orders, prevents overpayment (non-cash methods) and logs completion status when balance hits zero.

## Status Logging & Polling
- `OrderStatus` enum centralizes allowed transitions.
- `order_status_logs` table captures timeline; `OrderStatusController@show` returns current status + history payload ready for polling/websocket adapters.

## Data Model Changes
- **users**: added `phone`, `is_guest` columns.
- **orders**: added `user_id`, `notes`, `subtotal_order`, `discount_order`, `service_fee_order`, `total_order` (compatibility fallbacks if legacy cols exist).
- **order_status_logs**: separate table for event history.
- **sanctum**: config + `personal_access_tokens` migration.

Run migrations with:
```bash
php artisan migrate
```

## Controllers / Resources Layout
```
app/Http/Controllers/Api/V1/
  Auth/GuestAuthController.php
  MenuController.php
  CategoryController.php
  OrderController.php
  OrderItemController.php
  OrderStatusController.php
  PaymentController.php
app/Http/Resources/Api/V1/ (Menu, Category, Order, OrderItem, OrderStatus, Payment)
app/Http/Requests/Api/V1/ (... form requests ...)
```
Each controller returns typed API Resources, ensuring consistent JSON contracts.

## Sample Request/Response
### Create Order
```
POST /api/v1/orders
Authorization: Bearer <token>
{
  "order_type": "dine_in",
  "table_id": 3,
  "items": [
    {"menu_id": 10, "quantity": 2}
  ]
}
```
Response `201` (abridged):
```json
{
  "data": {
    "id": "12",
    "status": "draft",
    "totals": {"subtotal": 64000, "grand_total": 64000, "currency": "IDR"},
    "items": [
      {"id": "34", "product_id": "10", "quantity": 2, "unit_price": 32000, "subtotal": 64000}
    ]
  }
}
```

### Order Status Payload
```json
{
  "data": {
    "order_id": "12",
    "current_status": {"code": "preparing", "label": "Preparing", "updated_at": "2025-12-14T13:05:00Z"},
    "history": [
      {"code": "draft", "label": "Draft", "timestamp": "2025-12-14T13:00:00Z"},
      {"code": "pending", "label": "Pending", "timestamp": "2025-12-14T13:01:00Z"}
    ]
  }
}
```

### Store Config & Geofencing Payload
```
GET /api/v1/store-config
```
Response `200`:
```json
{
  "success": true,
  "data": {
    "store_name": "LakuPOS Cafe",
    "self_order_allow_cash": true,
    "geofence": {
      "enabled": true,
      "latitude": -6.2088,
      "longitude": 106.8456,
      "radius_meters": 50
    }
  }
}
```

### Idempotency & Stock Concurrency
- `POST /api/v1/orders` accepts `X-Idempotency-Key` header (UUID) to prevent duplicate charges or orders upon flaky mobile connections.
- Deductions on `ingredients` utilize conditional atomic decrement queries (`WHERE stock_qty >= needed`), returning HTTP 422 if any required item or topping ingredient is insufficient.
- Cancelling orders (`POST /api/v1/orders/{order}/cancel`) triggers automatic rollback of ingredients via `restoreIngredientsFromOrder()`.

## Testing & Validation
- Automated test suite runs via `php artisan test` (including `StoreConfigTest`, `KitchenDisplayTest`, `ReceiptSettingsTest`).
- UAT checklist covering 33 end-to-end scenarios is available in `docs/uat-checklist.md`.

## Deployment Checklist
1. `composer install --no-dev` (prod).
2. `php artisan migrate --force` (new tables/columns).
3. Ensure `.env` has `SANCTUM_STATEFUL_DOMAINS` (if SPA) and `SESSION_DOMAIN` configured.
4. Set up API base URL + tokens for frontend consumption.

## Future Extensions
- Plug real-time broadcasting (Laravel Echo) on `OrderStatusLog` creation.
- Expand payments with metadata per provider (`metadata` JSON column already available).
- Add rate limiting (`Route::middleware('throttle:api')`).
