# Multi-Tenancy Strategy

Model: **single database, shared schema, row-level isolation via `business_id`** (spec §12–13). No database-per-tenant, no schema-per-tenant — unnecessary complexity for the target scale (SME SaaS, not enterprise) and it would break simple cross-tenant reporting for Super Admin.

## 1. Resolving "current business"

A user (`business_users` row) may belong to more than one business (e.g. staff working two shops, or an owner with multiple shops). Session state therefore needs an **active business selector**, not just "the user's business":

- `session('current_business_id')` holds the active business for the web session.
- Middleware `App\Http\Middleware\ResolveCurrentBusiness` runs on every authenticated business route:
  1. Reads `current_business_id` from session.
  2. Verifies the authenticated user actually has an active `business_users` row for that business (never trust session blindly against a business the user was removed from).
  3. If missing/invalid, falls back to the user's first active business, or redirects to a "choose business" screen if the user has none/many-with-none-selected.
  4. Binds the resolved `Business` model into a singleton, `App\Support\Tenancy\CurrentBusiness`, resolvable app-wide via `app(CurrentBusiness::class)` or a `current_business()` helper.
- API requests (Sanctum tokens): the token is minted **scoped to one business** (ability list includes `business:{id}`); no session, no switcher — a mobile app that needs multi-business support requests a token per business.

## 2. Enforcing isolation at the model layer

- Trait `App\Models\Concerns\BelongsToBusiness`, applied to every tenant-scoped model:
  - Boots a global scope that adds `where business_id = current_business()->id` to every query automatically.
  - Auto-fills `business_id` on creation from the current business (never accepts `business_id` from request input).
  - Exposes `withoutTenantScope()` explicitly for the rare legitimate cross-tenant case (Super Admin views), so bypassing is always a deliberate, visible call, never a default.
- Route-model binding is **re-validated in a `FormRequest`/policy**, not trusted from the URL: e.g. `GET /orders/{order}` — even though the global scope already filters by business, every controller action additionally calls `$this->authorize('view', $order)` so a mismatched-business 404s/403s rather than silently leaking through a scope bug.

## 3. Super Admin vs tenant scope

Super Admin routes live under a separate `admin.*` route group with its own middleware (`is_super_admin`) and **do not** load `ResolveCurrentBusiness`. Admin controllers explicitly call `Business::withoutTenantScope()->...` — cross-tenant access is the intended behavior there and is always audit-logged.

## 4. Rider portal

A rider (`riders.user_id`) is authenticated like a business user but is restricted by policy to only their own `deliveries` rows within their business — they never see products, customers-at-large, financials, or other riders (spec §39).

## 5. Public store & guest checkout

The public store (`/shop/{slug}`) resolves the business from the **slug in the URL**, not from any session/auth — it is unauthenticated by design. `CurrentBusiness` is bound from the resolved `Business::where('slug', $slug)->firstOrFail()` for the duration of that request only. Guest carts/checkout sessions are tied to that business_id + a signed cookie session id, never to a logged-in user.

## 6. Mandatory automated test (spec §73 — release blocker)

`tests/Feature/TenantIsolationTest.php` must assert, for every major resource (products, customers, orders, invoices, payments, deliveries):

- Business A's authenticated user cannot `GET`/`PUT`/`DELETE` Business B's record by guessing/enumerating its ID (web + API).
- Business A's user cannot see Business B's records in list/search/export endpoints.
- Swapping `current_business_id` in session to a business the user does not belong to is rejected by `ResolveCurrentBusiness`.
- Sanctum tokens scoped to Business A are rejected on Business B's API resources even for the same underlying `user_id` (e.g. an owner of both).

This test suite must pass before Phase 1 is considered done, and is re-run at the end of every subsequent phase.
