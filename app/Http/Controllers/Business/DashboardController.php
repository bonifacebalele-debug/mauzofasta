<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Spec §19-21: today's metrics, quick actions, and alerts. The metric
     * queries below are correctly zero for a brand-new business — there is
     * no fake data here. They become real aggregate queries against
     * orders/payments/expenses once those modules exist (Phases 4-6); the
     * shape of the response already matches what those phases will fill in.
     */
    public function index()
    {
        $business = current_business();

        $metrics = [
            'sales_today' => 0,
            'orders_today' => 0,
            'paid_orders_today' => 0,
            'unpaid_orders_today' => 0,
            'expenses_today' => 0,
            'estimated_profit_today' => 0,
        ];

        $quickActions = [
            ['route' => 'orders.quickSale', 'icon' => 'ri-shopping-cart-2-line', 'label' => 'Uza'],
            ['route' => 'products.create', 'icon' => 'ri-price-tag-3-line', 'label' => 'Ongeza Bidhaa'],
            ['route' => 'customers.create', 'icon' => 'ri-user-add-line', 'label' => 'Ongeza Mteja'],
            ['route' => 'payments.create', 'icon' => 'ri-bank-card-line', 'label' => 'Rekodi Malipo'],
        ];

        return view('business.dashboard.index', [
            'business' => $business,
            'metrics' => $metrics,
            'quickActions' => $quickActions,
            'alerts' => $business?->activeAlerts() ?? [],
        ]);
    }
}
