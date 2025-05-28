<?php

return [
    // USER MANAGEMENT
    'users.view',
    'users.create',
    'users.edit',
    'users.delete',

    // ROLE MANAGEMENT
    'roles.view',
    'roles.create',
    'roles.edit',
    'roles.assign_permissions',

    // DISTRIBUTION CENTERS
    'distribution_centers.view',
    'distribution_centers.create',
    'distribution_centers.edit',
    'distribution_centers.delete',
    'distribution_center.manage_own',
    'distribution_center.assign_deliverers',

    // ORDERS
    'orders.view',
    'orders.create',
    'orders.edit',
    'orders.delete',
    'orders.assign',
    'orders.view_own',
    'orders.create_own',
    'orders.view_assigned',

    // DELIVERIES
    'deliveries.view',
    'deliveries.create',
    'deliveries.edit',
    'deliveries.delete',
    'deliveries.assign',
    'deliveries.view_own',
    'deliveries.edit_own',
    'deliveries.track_own',

    // BOTTLES
    'products.view',
    'products.create',
    'products.edit',
    'products.delete',
    'bottles.track',
    'bottles.scan',
    'bottles.assign',

    // BOTTLE TYPES
    'bottle_types.view',
    'bottle_types.create',
    'bottle_types.edit',
    'bottle_types.delete',

    // ACCESSORIES
    'accessories.view',
    'accessories.create',
    'accessories.edit',
    'accessories.delete',

    // ACCESSORY TYPES
    'accessory_types.view',
    'accessory_types.create',
    'accessory_types.edit',
    'accessory_types.delete',

    // SUPPLIERS
    'suppliers.view',
    'suppliers.create',
    'suppliers.edit',
    'suppliers.delete',
    'supplier_deliveries.view',
    'supplier_deliveries.create',
    'supplier_deliveries.edit',
    'supplier_deliveries.delete',

    // CUSTOMERS
    'customers.view',
    'customers.create',
    'customers.edit',
    'customers.delete',

    // PAYMENTS
    'payments.view',
    'payments.edit',

    // REPORTS AND ANALYTICS
    'reports.manage',

    // GAS MANAGEMENT
    'gas.manage',

    // COMMENTS
    'comments.view',
    'comments.moderate',

    // PROFILE
    'profile.edit',

    // HISTORY
    'history.view_own',

    // MOBILE ACCESS
    'mobile.access',

    // GEOLOCATION
    'delivery.track_location',
];
