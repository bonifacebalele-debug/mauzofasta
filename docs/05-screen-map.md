# Screen Map (Mobile-First, min width 360px)

Bottom navigation for the business app (5 max, thumb-reachable): **Mwanzo (Dashboard) · Mauzo (Sales/Orders) · Bidhaa (Products) · Wateja (Customers) · Zaidi (More → everything else)**.

```text
AUTH
  Login
  Register (5-step wizard: business name → owner → phone → email → category → location → logo → description → password → create) §17
  Forgot / reset password
  "Karibu MAUZO FASTA 👋" post-registration guided setup (add product → add customer → create order)

DASHBOARD (Mwanzo)
  Today's metrics cards: Mauzo Leo, Orders, Zilizolipwa, Zisizolipwa, Gharama, Faida ya Makadirio
  Quick actions: + Uza · + Ongeza Bidhaa · + Ongeza Mteja · + Rekodi Malipo
  Alerts feed: low stock, pending payments, pending deliveries, overdue invoices, subscription warnings

SALES (Mauzo)
  Orders list (filter by status, search)
  Order detail (items, totals, status timeline, payments, actions to advance status)
  Fast Sale screen: product picker → quantity → customer picker/create → payment → delivery → "Kamilisha Mauzo"

PRODUCTS (Bidhaa)
  Product list (grid on mobile, image + name + price + stock badge)
  Product detail/edit (images, variants, pricing, stock)
  Category management
  Stock movement log / manual adjustment screen

CUSTOMERS (Wateja)
  Customer list (search)
  Customer profile: orders, total spent, last order, outstanding balance, delivery history, notes

MORE (Zaidi)
  Payments list / record payment
  Invoices list / invoice detail (view, download PDF, send WhatsApp)
  Receipts list / receipt detail
  Expenses list / add expense
  Delivery: zones & fees, riders, deliveries board (kanban-style by status on tablet+, list on phone)
  Online store settings (activate store, banner, share link)
  Reports (sales, products, customers, payments, expenses, profit) — charts collapse to summary cards on phone
  Subscription & billing status
  Business settings (profile, staff & roles, hours, tax)
  Notifications
  Support tickets
  "Muuulize MAUZO FASTA" AI query box (Phase 12, optional feature — hidden entirely if AI provider not configured)

RIDER PORTAL (separate, simplified nav — no bottom nav needed, single list view)
  Today's Deliveries: Order # · Customer · Phone · Location · Amount · Status
  Buttons per delivery: Accept → Picked Up → On the Way → Delivered

SUPER ADMIN CONSOLE (desktop-oriented, but responsive)
  Platform dashboard: businesses, active/trial/paid counts, MRR, new businesses, orders, sales volume
  Businesses list/detail (activate/suspend/extend trial/change plan)
  Plans management
  Subscriptions list
  Support queue
  Audit logs
  Platform settings

PUBLIC STORE (guest, mobile-first storefront)
  Store home: logo, name, categories, search, product grid
  Product detail: images, price, "Ongeza" (add to cart)
  Cart drawer/page
  Checkout: name, phone, email(optional), region/district/area, notes, payment method → place order
  Order confirmation screen + WhatsApp share

MARKETING SITE (guest)
  Hero → Problem → Solution → Features → How it works → Who it's for → Pricing → Testimonials → FAQ → CTA → Footer
```

Empty states, confirmations, and low-stock/alert copy follow the Kiswahili-first tone examples given in spec §21, §81–82 verbatim where already specified.
