# POS Cafe – UAT Checklist

## Scope & Preconditions
- Environment mirrored to staging with latest migrations (`php artisan migrate --seed` if needed).
- Realistic seed data for categories, products, tables, toppings, and at least two cashier roles (admin & staff).
- Test users:
  - **Admin** (full access) for verifying analytics/widgets.
  - **Cashier** (no admin role) for restricted views.
- Browser cache cleared; run in Chrome/Edge latest.

## Test Matrix
| ID | Scenario | Steps | Expected Result |
|----|----------|-------|-----------------|
| UAT-01 | Create dine-in order with optional toppings | 1. Login as cashier. 2. Open **Order e Create**. 3. Set table, order type = Dine In. 4. Use Order Item Builder: pick product, qty, choose at least one topping, add discount. 5. Submit order. | Order saved with `subtotal_order` including topping prices minus discount, toppings listed per item, order status = Open and assigned to logged-in user. |
| UAT-02 | Edit existing order & adjust toppings | 1. From Order list card view, locate order from UAT-01. 2. Click a menu chip to open detail modal, verify toppings & totals. 3. Click **Kelola Order**. 4. Remove one topping and change qty. 5. Save. | Modal shows accurate data, edit page persists updated toppings/qty, recalculated totals reflected in card & list views. |
| UAT-03 | Validate stock enforcement | 1. Configure product ingredients with low stock. 2. Attempt to create order exceeding stock via builder. | Filament notification "Stok Tidak Cukup" shown, order not created, stock unchanged. |
| UAT-04 | Record payment and ensure totals | 1. Login as admin. 2. Open Payments e Create. 3. Select order, set payment method & amount = `total_order`. 4. Save. | Payment stored with `payment_date`, order status transitions to Completed (if workflow requires), payment appears in payment list. |
| UAT-05 | Dashboard widgets (Daily Top Orders + Sales Chart) | 1. Ensure at least two paid orders today. 2. Visit dashboard as admin. | Daily Top Orders table lists products sorted by qty; Sales chart shows today's revenue bar with total = sum of today's payments. |
| UAT-06 | Restricted cashier sees own sales fallback | 1. Login as cashier with no admin role. 2. Ensure at least one payment made by cashier today. 3. Visit dashboard. | Sales chart shows bar with cashier's totals; if cashier has no data, widget gracefully falls back to global totals while respecting UI access. |
| UAT-07 | Language session middleware | 1. Switch language via language switcher. 2. Reload dashboard & orders list. | Locale persists across pages, timestamps and currency follow selected locale. |

## Approval Sign-off
- Tester:
- Date:
- Result: PASS / FAIL (circle one)
- Notes / follow-ups:



## SUMARY
   •  Added docs/uat-checklist.md, a structured UAT
      plan covering key POS flows (order
      creation/editing with toppings, stock
      validation, payments, dashboard widgets, locale        
      persistence) plus sign-off metadata.
   •  Implemented feature tests: OrderToppingFlowTest        
      ensures order items persist optional toppings
      and totals, while DashboardWidgetsTest verifies        
      the Sales chart widget for both admin and
      restricted users (including fallback behavior).
   •  All tests, including the new suites, pass via
      php artisan test.
