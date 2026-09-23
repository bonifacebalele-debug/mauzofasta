# Route Map & API Map

## 1. Web routes (`routes/web.php`, session/`web` guard)

The existing generic wildcard router (`RoutingController::root/secondLevel/thirdLevel`) is retired for product routes (see decision D1) — every route below is explicit and named, so per-route middleware/policies/breadcrumbs work correctly.

```text
/                                   marketing.home        (guest, public)
/pricing, /features, /about ...     marketing.*            (guest, public)

/register                           business.register      (guest) — 5-step onboarding wizard (§17)
/login, /logout, /forgot-password   auth.*                 (Laravel auth scaffolding)

/app  (auth + ResolveCurrentBusiness middleware group, prefix "app")
    /app/switch-business/{business}         business.switch
    /app                                    dashboard.index
    /app/products[...]                      products.*          (resource + variants + images sub-routes)
    /app/categories[...]                    categories.*
    /app/stock/movements, /adjustments      stock.*
    /app/customers[...]                     customers.*
    /app/orders[...]                        orders.*
    /app/orders/quick-sale                  orders.quickSale    (Fast Sale screen, §27)
    /app/payments[...]                      payments.*
    /app/invoices[...]                      invoices.*           /{invoice}/pdf, /{invoice}/send-whatsapp
    /app/receipts[...]                      receipts.*           /{receipt}/pdf, /{receipt}/send-whatsapp
    /app/expenses[...]                      expenses.*
    /app/delivery/zones, /fees              delivery.zones.*
    /app/riders[...]                        riders.*
    /app/deliveries[...]                    deliveries.*
    /app/store/settings                     store.settings
    /app/reports/sales|products|customers|payments|expenses|profit   reports.*
    /app/settings/business|users|hours|tax  settings.*
    /app/subscription                       subscription.index
    /app/notifications                      notifications.index
    /app/support/tickets[...]               support.*

/rider  (auth + rider-role middleware, no ResolveCurrentBusiness switcher — single business fixed to rider)
    /rider                                   rider.dashboard  ("Today's Deliveries")
    /rider/deliveries/{delivery}/accept|pickup|out-for-delivery|delivered   rider.deliveries.*

/admin  (auth + is_super_admin middleware)
    /admin                                   admin.dashboard
    /admin/businesses[...]                   admin.businesses.*   suspend/activate/extend-trial actions
    /admin/plans[...]                        admin.plans.*
    /admin/subscriptions[...]                admin.subscriptions.*
    /admin/support[...]                      admin.support.*
    /admin/audit-logs                        admin.audit-logs.index
    /admin/settings                          admin.settings

/shop/{business:slug}                        store.show           (public storefront, no auth)
/shop/{business:slug}/products/{product}      store.product
/shop/{business:slug}/cart                    store.cart.*
/shop/{business:slug}/checkout                 store.checkout.*
```

## 2. API routes (`routes/api.php`, prefix `/api/v1`, `sanctum` guard)

Mirrors the web app's business resources for future Android/iOS clients (spec §20, §78):

```text
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/me                        current user + current business + role

GET|POST            /api/v1/products
GET|PUT|DELETE       /api/v1/products/{product}
GET|POST             /api/v1/customers
GET|PUT|DELETE       /api/v1/customers/{customer}
GET|POST             /api/v1/orders
GET                  /api/v1/orders/{order}
POST                 /api/v1/orders/{order}/payments
GET                  /api/v1/invoices/{invoice}
GET                  /api/v1/invoices/{invoice}/pdf
GET                  /api/v1/receipts/{receipt}
GET                  /api/v1/deliveries?assigned_to=me        (rider app)
POST                 /api/v1/deliveries/{delivery}/status
GET                  /api/v1/reports/sales?range=today|week|month|custom
```

Every endpoint: `FormRequest` validation → Policy authorization → API Resource for the response. Standard envelope per spec §79 (`success/message/data` or `success/message/errors`), paginated with Laravel's default `links`/`meta`.

## 3. Public store WhatsApp deep link

`GET /shop/{slug}/products/{product}` renders an **"Agiza WhatsApp"** button that opens `https://wa.me/{business.whatsapp_number}?text={urlencoded contextual message}` (spec §46) — no API call needed for this simplest case; full conversational ordering (spec §44) is built in Phase 9 behind `WhatsAppServiceInterface`.
