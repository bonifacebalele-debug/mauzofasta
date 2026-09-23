# Folder Structure & Service Architecture

Standard Laravel conventions, grouped by area under `Http/Controllers` rather than a heavier DDD/module system — the spec explicitly warns against building a huge ERP (§23) and prefers speed/simplicity (§117), so we avoid over-engineering the folder structure.

```text
app/
  Console/
  Exceptions/
  Http/
    Controllers/
      Marketing/            (public site)
      Business/              (Products, Orders, Customers, Payments, Invoices,
                               Receipts, Expenses, Delivery, Riders, Reports,
                               Settings, Subscription, Notifications, Support)
      Rider/
      Admin/
      Store/                 (public storefront + cart + checkout)
      Api/V1/                (mirrors Business controllers, returns API Resources)
      Auth/                  (existing scaffolding, extended for business registration wizard)
    Middleware/
      ResolveCurrentBusiness.php
      EnsureSuperAdmin.php
      EnsureSubscriptionActive.php   (gates paid-plan-only features per plan_features)
    Requests/
      Business/... (one per module: StoreProductRequest, UpdateOrderRequest, ...)
    Resources/       (API resources, one per exposed model)
  Models/
    Concerns/BelongsToBusiness.php
    Concerns/HasUuid.php
    ... (one model per table in 01-database-erd.md)
  Policies/           (one per tenant-scoped model)
  Services/
    Payment/
      PaymentServiceInterface.php
      ManualPaymentService.php
    WhatsApp/
      WhatsAppServiceInterface.php
      LogOnlyWhatsAppService.php      (no-op/dev fallback — logs instead of sending)
      MetaCloudApiWhatsAppService.php (Phase 9, behind env config)
    Ai/
      AiServiceInterface.php
      NullAiService.php                (default: AI features hidden if unconfigured)
    Pdf/
      InvoicePdfService.php
      ReceiptPdfService.php
  Actions/            (single-purpose, testable use-cases for multi-step writes)
    Orders/CompleteSaleAction.php       (the Fast Sale "COMPLETE SALE" flow, §27)
    Orders/RecalculateOrderTotalsAction.php
    Delivery/AssignRiderAction.php
    Subscriptions/StartTrialAction.php
  Support/
    Tenancy/CurrentBusiness.php
    Money.php                          (integer-TZS helpers, formatting "Tsh 35,000")
  Providers/
    ...
resources/
  views/
    layouts/            (existing Bootstrap theme partials, retained for authenticated app)
    marketing/
    business/            (namespaced per module, matches Business controllers)
    rider/
    admin/
    store/               (own lightweight layout, not the full admin theme)
    emails/, pdf/
routes/
  web.php  (marketing + auth includes)
  business.php  (the "/app" group)
  rider.php
  admin.php
  store.php
  api.php
database/
  migrations/
  factories/
  seeders/
docs/   (this document set, expanded per-phase per spec §119)
tests/
  Feature/   (per module, plus TenantIsolationTest.php)
  Unit/      (Actions, Services, Money)
```

## Service interfaces (provider-independent, per spec rules 6–9)

```php
interface PaymentServiceInterface {
    public function recordPayment(Order|Invoice $payable, PaymentData $data): Payment;
    public function confirm(Payment $payment): Payment;
    public function refund(Payment $payment, int $amount): Payment;
}

interface WhatsAppServiceInterface {
    public function sendOrderConfirmation(Order $order): void;
    public function sendInvoice(Invoice $invoice): void;
    public function sendReceipt(Receipt $receipt): void;
    public function sendPaymentConfirmation(Payment $payment): void;
    public function sendDeliveryUpdate(Delivery $delivery): void;
    public function sendCustomerReminder(Customer $customer, string $template, array $data): void;
}

interface AiServiceInterface {
    public function ask(Business $business, string $question): AiAnswer; // scoped strictly to that business's data
}
```

Each is bound in a `ServiceProvider` to a concrete implementation chosen by `config('services.whatsapp.provider')` / `config('services.ai.provider')` — read from env (`WHATSAPP_PROVIDER`, `AI_PROVIDER`), never hard-coded (spec rules 7–9). If unconfigured, the bound implementation is a safe no-op/log-only class so the rest of the app keeps working (spec §50, "AI must remain optional").

## Fast Sale flow (§27) as an Action, not fat controller logic

`CompleteSaleAction::execute(business, items[], customer, paymentData, deliveryData)`:
1. Recalculates every line total + order total server-side (never trusts posted totals).
2. Wraps steps 2–7 in a DB transaction: create order → insert order_items → insert stock_movements (type=sale) & decrement stock → record payment (via `PaymentServiceInterface`) → generate invoice → generate receipt if fully paid → dispatch `OrderCreated` event (queued listeners update dashboard caches, log notification, optionally trigger WhatsApp confirmation).
5. Returns the completed `Order` for the UI to redirect to the order detail/receipt screen.
