# MAUZO FASTA — Technical Blueprint (Phase 0)

Status: **DRAFT FOR REVIEW — no Phase 1 code written yet.**
Scope: satisfies Master Specification §93/§120–121 (architecture must be reviewed and approved before implementation begins).

## 1. Starting point (as found in this repo)

This directory is **not** an empty Laravel skeleton. It already contains:

| Item | Actual state |
|---|---|
| Framework | Laravel **10.10**, PHP `^8.1` in composer.json (local PHP is 8.3.6, Composer 2.10.2) |
| Auth | Stock Laravel auth scaffolding (`AuthenticatedSessionController`, `RegisteredUserController`, etc. — Breeze-pattern controllers, no Breeze package listed in composer.json) |
| API | `laravel/sanctum ^3.2` installed, `personal_access_tokens` migration present, `routes/api.php` present but empty of business routes |
| Frontend theme | A purchased/generic Bootstrap 5 admin dashboard kit (`admin-resources` from coderthemes, i.e. a Domiex/Skote-family template) — SCSS design system, DataTables, ApexCharts, Select2, Flatpickr, Dropzone, etc. Views under `resources/views/{ui,tables,forms,charts,pages,layouts}` are the template's generic demo pages (buttons, cards, invoice mock-up, pricing mock-up, etc.), not MAUZO FASTA screens. |
| Routing | `routes/web.php` uses a **generic wildcard router** (`RoutingController::root/secondLevel/thirdLevel`) that maps any URL segment(s) directly to a Blade view of the same dotted name (e.g. `/ui/buttons` → `ui.buttons`). This is a template convenience for browsing demo pages — it has no concept of business scoping, policies, or per-module authorization. |
| Database | Only the default `users`, `password_reset_tokens`, `failed_jobs`, `personal_access_tokens` tables. Nothing MAUZO-FASTA-specific yet. |
| Data | No demo/seed data for a real business. |

**Conclusion:** the Bootstrap theme (SCSS + JS asset pipeline + layout partials) is a reasonable, spec-compliant UI foundation ("Blade + Bootstrap 5 + JavaScript") and is worth keeping for its visual design system. The **routing, controllers, auth flow, and database are all being built new** for MAUZO FASTA — the wildcard router and demo pages are not part of the product and will not be used for real business routes. This is flagged as Decision D1 in [08-ambiguities-decisions.md](08-ambiguities-decisions.md).

## 2. Stack (per spec, with gaps called out)

| Layer | Spec requirement | Decision |
|---|---|---|
| Backend | Laravel 12 | **Needs upgrade** from 10.10 → 12 before Phase 1 migrations are written (see D1). PHP requirement moves `^8.1` → `^8.3` (already satisfied locally). |
| DB | MySQL 8+ | Use as-is; `.env.example` currently defaults to sqlite for the stock skeleton — will be corrected to MySQL per spec §88. |
| Frontend | Blade + Bootstrap 5 + JS | Keep existing Bootstrap 5 theme assets as the design system for the authenticated app (dashboard, business console, admin, rider portal). Public marketing site and public store get their own lightweight, fast-loading Blade layout (mobile-first storefront doesn't need the full admin theme's JS payload). |
| API | REST `/api/v1` | Laravel Sanctum (already installed) issues personal access tokens scoped to one business each, for future Android/iOS clients. |
| Multi-tenancy | Shared DB, `business_id` on tenant tables | Custom global-scope + middleware solution (no third-party multi-tenancy package needed at this scale) — see [02-multitenancy.md](02-multitenancy.md). |
| Roles/permissions | roles, permissions, role_permissions tables | `spatie/laravel-permission` (industry-standard, matches the exact table shapes the spec lists) — flagged as D2, needs approval since it's a new dependency. |
| PDF (invoices/receipts) | "professional invoice generation" | `barryvdh/laravel-dompdf` — pure-PHP, no external binary, works on WAMP/XAMPP/Herd/Linux without extra system packages — flagged as D3. |
| Queue | queues for mail/WhatsApp/PDF/reports | Database queue driver by default (zero extra infra for local dev), Redis optional via `.env` for production — flagged as D4. |
| PWA | installable, offline shell | `laravel-pwa`-style manual manifest + service worker (no heavy package) — built in Phase 8/13, not Phase 1. |

## 3. Module → Phase map (unchanged from spec §93–107)

```
Phase 0  Architecture (this document set)                                     ✅ done
Phase 1  Foundation: auth, multi-tenancy, roles, business registration        ✅ done (22 tests passing on sqlite — see note below)
Phase 2  Dashboard                                                            ✅ done (real Bootstrap 5 theme shell wired in)
Phase 3  Products & Inventory                                                 ✅ done (36 tests passing on sqlite + MySQL)  ← we are here
Phase 4  Customers & Sales (Fast Sale)
Phase 5  Payments & Documents (invoices, receipts, PDFs)
Phase 6  Expenses & Reports
Phase 7  Delivery (zones, riders, tracking)
Phase 8  Online Store (public storefront, cart, checkout)
Phase 9  WhatsApp integration (provider-abstracted)
Phase 10 Subscriptions (plans, trials, limits)
Phase 11 Super Admin
Phase 12 AI Assistant (provider-abstracted, optional)
Phase 13 Hardening (security, tenant-isolation, performance, mobile)
Phase 14 Production deployment
```

Each phase ends with automated tests passing before the next phase starts (spec rule 27).

**Phase 1 note (resolved 2026-09-23):** `php artisan migrate --seed` was run against
the real `mauzofasta` MySQL 8.3.0 database once WAMP was started, and it surfaced a
real bug: this WAMP install's `my.ini` sets `default_storage_engine=MyISAM`
(non-standard — MySQL has defaulted to InnoDB since 5.5). Laravel's `mysql`
connection had `'engine' => null`, so it inherited that default — MyISAM has no
foreign keys and a 1000-byte key length cap, which broke the very first migration
(`users_email_unique`). Fixed by forcing `'engine' => 'InnoDB'` in
[config/database.php](../config/database.php), independent of the server's
default. All tables now correctly create as InnoDB, and the full test suite
(22 tests) passes both on sqlite (`phpunit.xml` default) and re-pointed at real
MySQL. **Carry this into the Phase 14 production deployment checklist**: don't
assume a target server's `default_storage_engine` is InnoDB — the explicit config
override makes this moot going forward, but it's worth a note in
[07-testing-deployment-strategy.md](07-testing-deployment-strategy.md) for anyone
provisioning a new server by hand.

**Phase 2 note:** the authenticated app shell now uses the real kept Bootstrap 5
theme layout (`layouts/vertical.blade.php` + `layouts/shared/{topbar,left-sidebar,footer}`)
rather than the Phase 1 placeholder minimal layout, per the "keep the theme"
decision in D1. This required removing more purchased-theme demo content that
had leaked into shared partials used app-wide: a fake language switcher, fake
message/notification dropdowns (hardcoded names/avatars), a fake logged-in user
("Thomson"), a theme-customizer offcanvas that linked to the theme's own Envato
purchase page, and "Velonic"/"Techzaa" branding baked into the page-title
breadcrumb and footer. The sidebar now lists every module from
[05-screen-map.md](05-screen-map.md), but only `dashboard.index` currently
resolves — every other item renders disabled with a "Hivi Karibuni" (Coming
Soon) badge via `Route::has()`, so there are no dead links as later phases land
(spec rule 29). Dashboard metric cards run real (currently zero) aggregate
placeholders — they become live queries once Orders/Payments/Expenses exist in
Phases 4-6. The one already-real alert is subscription-trial status
(`Business::activeAlerts()`), shared between the topbar bell and the dashboard
body. Verified by curl against a live `php artisan serve` instance (registered
a real business through the wizard, fetched `/app`, confirmed the compiled
Bootstrap CSS/JS load with 200s and zero leftover theme-demo strings) since no
Node.js/browser-automation tooling was available in this environment — full
pixel-level visual QA in your own browser is still worth doing.

**Phase 2 addendum — real-browser bug found and fixed:** viewing the live dashboard
surfaced two things curl couldn't: (1) the compiled theme CSS referenced fonts/images
with absolute paths (`url(/build/assets/...)`), which broke under the WAMP
`/mauzofasta` subdirectory setup — fixed by patching the built CSS to same-directory
relative paths (`url(remixicon-....woff2)`), portable across subdirectory and
domain-root hosting alike, no rebuild needed. (2) Setting `APP_URL` in `.env` to
`http://localhost/mauzofasta` for that same WAMP setup broke the entire automated
test suite (`route('login')` built `http://localhost/mauzofasta/login`, and
PHPUnit's test client matches that whole string against routes registered as
`/login` — no route knows about `/mauzofasta`, so every test 404'd). Fixed by
pinning `APP_URL=http://localhost` inside `phpunit.xml`'s own `<php>` block,
same technique already used there for `DB_CONNECTION`/`DB_DATABASE` — the real
dev `.env` is untouched. **If you ever change `APP_URL` in `.env` again, re-run
the test suite immediately** — this class of bug is silent until then.

## 4. High-level system diagram

```mermaid
flowchart LR
    subgraph Guests
        MKT[Marketing site<br/>mauzofasta.co.tz]
        STORE[Public store<br/>/shop/{slug}]
    end

    subgraph App["app.mauzofasta.co.tz (web guard, session)"]
        DASH[Business Dashboard]
        RIDER[Rider Portal]
        ADMIN[Super Admin Console]
    end

    subgraph API["/api/v1 (sanctum guard, tokens)"]
        APIROUTES[REST endpoints]
    end

    subgraph Core["Laravel Application"]
        MW[ResolveCurrentBusiness middleware]
        SVC[Service layer:<br/>PaymentService, WhatsAppService, AIService]
        MODELS[Eloquent models<br/>+ BelongsToBusiness global scope]
    end

    DB[(MySQL: shared schema,<br/>business_id per tenant table)]
    QUEUE[(Queue: DB/Redis)]

    MKT --> Core
    STORE --> Core
    DASH --> MW --> MODELS
    RIDER --> MW --> MODELS
    ADMIN -.bypasses tenant scope.-> MODELS
    APIROUTES --> MW
    Core --> SVC
    SVC --> QUEUE
    MODELS --> DB
```

## 5. Non-functional requirements carried into every phase

- Every financial total is recalculated server-side (§28, §72) — never trust client-submitted totals.
- Every tenant-scoped query goes through the `BelongsToBusiness` scope; raw/DB::table queries touching tenant tables must manually filter by `business_id` and are reviewed accordingly.
- Every state transition (order status, delivery status, subscription status) writes a history row.
- Every privileged/administrative action writes an `audit_logs` row.
- Mobile-first: no core workflow requires horizontal scrolling at 360px width.
- Money is stored as integer minor units are **not** used for TZS (TZS has no subunit in practice) — stored as `unsignedBigInteger` whole shillings, never `float`/`double`.

See companion documents:
- [01-database-erd.md](01-database-erd.md) — full table/relationship specification
- [02-multitenancy.md](02-multitenancy.md) — tenant isolation strategy
- [03-permissions.md](03-permissions.md) — role/permission matrix
- [04-routes-api-map.md](04-routes-api-map.md) — web + API route map
- [05-screen-map.md](05-screen-map.md) — mobile screen map
- [06-folder-structure-service-architecture.md](06-folder-structure-service-architecture.md)
- [07-testing-deployment-strategy.md](07-testing-deployment-strategy.md)
- [08-ambiguities-decisions.md](08-ambiguities-decisions.md) — decisions needing your approval
