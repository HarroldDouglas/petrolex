<?php

use App\Enums\UserRole;

return [
    'roles' => [
        UserRole::SUPER_ADMIN()->value => [
            // Has all permissions through Gate::before implementation
        ],

        UserRole::ADMIN()->value => [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.assign_permissions',
            'distribution_centers.view', 'distribution_centers.create',
            'distribution_centers.edit', 'distribution_centers.delete',
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.assign',
            'deliveries.view', 'deliveries.create', 'deliveries.edit', 'deliveries.delete', 'deliveries.assign',
            'bottles.view', 'bottles.create', 'bottles.edit', 'bottles.delete', 'bottles.track',
            'bottle_types.view', 'bottle_types.create', 'bottle_types.edit', 'bottle_types.delete',
            'accessories.view', 'accessories.create', 'accessories.edit', 'accessories.delete',
            'accessory_types.view', 'accessory_types.create', 'accessory_types.edit', 'accessory_types.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'supplier_deliveries.view', 'supplier_deliveries.create', 'supplier_deliveries.edit', 'supplier_deliveries.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'reports.view', 'reports.export', 'analytics.view',
            'gas.manage',
            'comments.view', 'comments.moderate',
        ],

        UserRole::MANAGER()->value => [
            'users.view', 'users.create', 'users.edit',
            'distribution_centers.view', 'distribution_centers.create', 'distribution_centers.edit',
            'orders.view', 'orders.create', 'orders.edit', 'orders.assign',
            'deliveries.view', 'deliveries.create', 'deliveries.edit', 'deliveries.assign',
            'bottles.view', 'bottles.create', 'bottles.edit', 'bottles.track',
            'customers.view', 'customers.create', 'customers.edit',
            'reports.view', 'reports.export', 'analytics.view',
        ],

        UserRole::ACCOUNTANT()->value => [
            'orders.view',
            'deliveries.view',
            'reports.view', 'reports.export',
            'analytics.view',
            'payments.view', 'payments.edit',
            'customers.view',
            'comments.view',
        ],

        UserRole::GAS_MANAGER()->value => [
            'gas.manage',
            'bottles.view', 'bottles.create', 'bottles.edit', 'bottles.track',
            'bottle_types.view', 'bottle_types.edit',
            'suppliers.view',
            'supplier_deliveries.view', 'supplier_deliveries.create', 'supplier_deliveries.edit',
            'reports.view',
        ],

        UserRole::CENTER_MANAGER()->value => [
            'distribution_center.manage_own',
            'distribution_center.assign_deliverers',
            'users.view',
            'orders.view', 'orders.edit', 'orders.assign',
            'deliveries.view', 'deliveries.create', 'deliveries.edit', 'deliveries.assign',
            'bottles.view', 'bottles.edit', 'bottles.track', 'bottles.assign',
            'accessories.view', 'accessories.edit',
            'supplier_deliveries.view', 'supplier_deliveries.create', 'supplier_deliveries.edit',
            'reports.view',
            'customers.view',
        ],

        UserRole::DELIVERY_PERSON()->value => [
            'deliveries.view_own', 'deliveries.edit_own',
            'bottles.view', 'bottles.scan', 'bottles.track',
            'orders.view_assigned',
            'mobile.access',
            'delivery.track_location',
        ],

        UserRole::CUSTOMER()->value => [
            'orders.view_own', 'orders.create_own',
            'deliveries.track_own',
            'profile.edit',
            'history.view_own',
        ],
    ],

    'permissions' => [
        // USER MANAGEMENT
        'users.view', 'users.create', 'users.edit', 'users.delete',

        // ROLE MANAGEMENT
        'roles.view', 'roles.create', 'roles.edit', 'roles.assign_permissions',

        // DISTRIBUTION CENTERS
        'distribution_centers.view', 'distribution_centers.create',
        'distribution_centers.edit', 'distribution_centers.delete',
        'distribution_center.manage_own', 'distribution_center.assign_deliverers',

        // ORDERS
        'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
        'orders.assign', 'orders.view_own', 'orders.create_own', 'orders.view_assigned',

        // DELIVERIES
        'deliveries.view', 'deliveries.create', 'deliveries.edit', 'deliveries.delete',
        'deliveries.assign', 'deliveries.view_own', 'deliveries.edit_own', 'deliveries.track_own',

        // BOTTLES
        'bottles.view', 'bottles.create', 'bottles.edit', 'bottles.delete',
        'bottles.track', 'bottles.scan', 'bottles.assign',

        // BOTTLE TYPES
        'bottle_types.view', 'bottle_types.create', 'bottle_types.edit', 'bottle_types.delete',

        // ACCESSORIES
        'accessories.view', 'accessories.create', 'accessories.edit', 'accessories.delete',

        // ACCESSORY TYPES
        'accessory_types.view', 'accessory_types.create', 'accessory_types.edit', 'accessory_types.delete',

        // SUPPLIERS
        'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
        'supplier_deliveries.view', 'supplier_deliveries.create',
        'supplier_deliveries.edit', 'supplier_deliveries.delete',

        // CUSTOMERS
        'customers.view', 'customers.create', 'customers.edit', 'customers.delete',

        // PAYMENTS
        'payments.view', 'payments.edit',

        // REPORTS AND ANALYTICS
        'reports.view', 'reports.export', 'analytics.view',

        // GAS MANAGEMENT
        'gas.manage',

        // COMMENTS
        'comments.view', 'comments.moderate',

        // PROFILE
        'profile.edit',

        // HISTORY
        'history.view_own',

        // INVENTORY
        'inventory.view', 'inventory.edit',

        // MOBILE ACCESS
        'mobile.access',

        // GEOLOCATION
        'delivery.track_location',
    ],
];
