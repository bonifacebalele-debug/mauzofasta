# Decisions Needing Approval Before Phase 1

Per spec §120 step 10–11: these are the genuine ambiguities found while surveying the actual repo state. Everything else in this document set is a reasonable default inferred from the spec and is **not** blocking — flag if you disagree with any of it, but it doesn't require a decision to proceed.

**Status: D1, D2, D4 confirmed by owner on 2026-09-22 — all recommended options approved. Phase 1 implementation is now in progress.**

## D1 — Existing template mismatch (blocking, highest impact) — ✅ CONFIRMED: upgrade in place to Laravel 12

The repo is Laravel **10.10** (spec mandates **12**), with a purchased Bootstrap 5 admin theme (`admin-resources`/coderthemes) whose demo pages are served through a generic wildcard router (`RoutingController`) that has no tenant scoping or per-module authorization — incompatible with the multi-tenant, policy-gated routing this spec requires.

**Proposed resolution:** upgrade `laravel/framework` 10 → 12 (and `sanctum` 3 → 4, PHP constraint `^8.1` → `^8.3`; local PHP 8.3.6 already supports this), keep the Bootstrap 5 theme's SCSS/JS/layout partials as the visual design system for the authenticated app, and **replace** the wildcard router + demo-page controllers with the explicit named routes in [04-routes-api-map.md](04-routes-api-map.md). Demo/component-showcase pages (`ui/*`, `tables/*`, `forms/*`, `charts/*`) are deleted or moved behind a dev-only `/ui-kit` prefix if you want to keep them as a component reference while building.

## D2 — Roles & permissions package — ✅ CONFIRMED: spatie/laravel-permission + dompdf

Spec lists `roles`/`permissions`/`role_permissions` tables (§14) but doesn't mandate an implementation. Proposal: `spatie/laravel-permission` — it's the de facto standard, its default table shapes match what's specified, and it avoids reinventing permission caching/guard handling. Needs approval as a new composer dependency (spec rule 1 covers "core stack", this is more of a utility library, but flagging since it touches auth).

## D3 — PDF generation library

Spec requires invoice/receipt PDFs (§32–33) without naming a library. Proposal: `barryvdh/laravel-dompdf` (pure PHP, no wkhtmltopdf/Chromium binary to install on WAMP/XAMPP/Herd/Linux). Alternative would be `spatie/browsershot` (needs a headless Chrome binary — heavier local setup, nicer CSS fidelity). Recommend dompdf for V1 given spec §49's "don't generate huge PDFs synchronously" and simple invoice layouts.

## D4 — Branch-level inventory scope — ✅ CONFIRMED: business-level stock in V1

Spec lists a `branches` entity and a Pro-plan "multiple branches" feature, but does **not** list a per-branch stock table, and §108 excludes "complex warehouse management" from V1. Proposal: in V1, stock is tracked at the **business level** (one `stock_quantity` per product/variant, shared across all branches); `branches` in V1 is informational (location, hours, staff assignment) only. Per-branch inventory becomes a V2 item, matching the roadmap's "multi-branch, advanced inventory" (§109 V2). Confirm this matches your intent for what "Pro plan multiple branches" means in V1.

## D5 — Queue driver default

Spec allows Redis "if appropriate" (§75). Proposal: `database` queue driver by default (zero extra local infrastructure on WAMP/XAMPP), Redis wired in for production via env only. No action needed unless you want Redis mandatory even locally.

---

**Nothing beyond documentation has been written yet.** Once D1–D4 are confirmed (D5 is a non-blocking default), Phase 1 begins: Laravel 12 upgrade, migrations for the Foundation module (auth, businesses, business_users, roles/permissions, business registration wizard), and the TenantIsolationTest scaffold — per [00-technical-blueprint.md](00-technical-blueprint.md) §3.
