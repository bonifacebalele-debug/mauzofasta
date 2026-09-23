<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the fixed platform role set (spec §11) and the module.action
 * permission matrix (docs/03-permissions.md). Roles are platform-defined —
 * only the *assignment* of a role to a staff member is per-business
 * (App\Models\BusinessUser::role_id).
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /** @var array<string, string[]> */
    protected array $matrix = [
        'owner' => ['*'],
        'manager' => [
            'business.view',
            'staff.view', 'staff.create', 'staff.update', 'staff.manage',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'stock.adjust',
            'customers.view', 'customers.create', 'customers.update',
            'orders.view', 'orders.create', 'orders.update', 'orders.apply_discount',
            'payments.view',
            'invoices.view',
            'receipts.view',
            'expenses.view',
            'delivery.zones.manage', 'riders.manage', 'deliveries.view', 'deliveries.assign',
            'store.settings.manage',
            'reports.view',
            'subscription.view',
            'whatsapp.settings.manage',
            'support.tickets.manage',
        ],
        'sales_staff' => [
            'products.view',
            'customers.view', 'customers.create', 'customers.update',
            'orders.view', 'orders.create', 'orders.update',
            'payments.view', 'payments.create',
            'invoices.view', 'invoices.create',
            'receipts.view',
            'deliveries.view',
            'support.tickets.manage',
        ],
        'stock_manager' => [
            'products.view', 'products.create', 'products.update', 'products.delete',
            'stock.adjust',
            'customers.view',
            'orders.view',
            'reports.stock.view',
            'support.tickets.manage',
        ],
        'accountant' => [
            'customers.view',
            'orders.view',
            'payments.view', 'payments.create', 'payments.update', 'payments.delete',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',
            'receipts.view', 'receipts.create', 'receipts.update', 'receipts.delete',
            'expenses.view', 'expenses.create', 'expenses.update', 'expenses.delete',
            'reports.view', 'reports.financial.view',
            'subscription.view',
            'support.tickets.manage',
        ],
        'rider' => [
            'customers.view',
            'deliveries.view', 'deliveries.update_own',
            'support.tickets.manage',
        ],
    ];

    public function run(): void
    {
        $permissionNames = collect($this->matrix)
            ->flatten()
            ->reject(fn ($name) => $name === '*')
            ->unique()
            ->values();

        $permissionNames->each(
            fn (string $name) => Permission::findOrCreate($name, 'web')
        );

        foreach ($this->matrix as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            $role->syncPermissions(
                in_array('*', $permissions, true) ? Permission::all() : $permissions
            );
        }
    }
}
