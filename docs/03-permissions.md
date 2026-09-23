# Permission Matrix

Implemented with `spatie/laravel-permission` (pending approval — decision D2). Permission names follow `module.action`. A role's permission set is seeded by default but is **editable per business is out of scope for V1** — roles are platform-defined (spec §11 lists a fixed role set); only the *assignment* of a role to a staff member is per-business.

Legend: **C**reate, **R**ead, **U**pdate, **D**elete, **S**pecial (module-specific privileged action).

| Module | Owner | Manager | Sales Staff | Stock Manager | Accountant | Rider |
|---|---|---|---|---|---|---|
| Business settings / profile | CRUD | R | – | – | – | – |
| Staff & roles | CRUD | R | – | – | – | – |
| Delete business / transfer ownership | S | – | – | – | – | – |
| Products & categories | CRUD | CRUD | R | CRUD | – | – |
| Stock adjustments | CRUD | CRUD | – | CRUD | – | – |
| Customers | CRUD | CRUD | CR U | R | R | R (own delivery's customer only) |
| Orders (Fast Sale) | CRUD | CRUD | CR U | R | R | – |
| Apply discount (`orders.apply_discount`) | S | S | – (unless granted) | – | – | – |
| Payments | CRUD | R | CR | – | CRUD | – |
| Invoices | CRUD | R | CR | – | CRUD | – |
| Receipts | CRUD | R | R | – | CRUD | – |
| Expenses | CRUD | R | – | – | CRUD | – |
| Delivery zones / fees | CRUD | CRUD | – | – | – | – |
| Riders (manage) | CRUD | CRUD | – | – | – | – |
| Deliveries (assign) | CRUD | CRUD | R | – | – | S (accept/update own only) |
| Online store settings | CRUD | CRUD | – | – | – | – |
| Reports (all) | R | R | – | R (stock only) | R (financial only) | – |
| Subscription & billing | CRUD | R | – | – | R | – |
| WhatsApp / notification settings | CRUD | CRUD | – | – | – | – |
| Support tickets | CRUD | CR | CR | CR | CR | CR |

`apply_discount` is modeled as its own permission (spec §29) so an Owner/Manager can grant it selectively to a Sales Staff member without giving full order-management rights.

## Platform-level role

**Super Administrator** is not a `business_users` row at all — it's `users.is_super_admin = true`, authorized via a dedicated `SuperAdminPolicy`/gate, with access to: businesses, plans, subscriptions, platform analytics, support, audit logs, platform settings (spec §56–57). Enforced with strong session rules (short idle timeout) and every action written to `audit_logs`. 2FA is future-ready (a `two_factor_secret` column reserved on `users` from Phase 1, UI deferred).

## Enforcement layers (defense in depth)

1. **Route middleware** — `permission:products.create` style gate on route groups.
2. **Policies** — `ProductPolicy::update($user, $product)` etc. re-checks both the permission *and* that `$product->business_id` matches the user's current business (belt-and-braces alongside the global scope).
3. **Form Requests** — `authorize()` delegates to the same policy; validation never doubles as authorization.
4. **Blade** — `@can('products.create')` hides actions the user can't perform, but is never the only check (UI hiding is not security).
