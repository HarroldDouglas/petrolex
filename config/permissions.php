<?php

// config/permissions.php

return [
    'roles' => [
        'super_admin' => [
            'name' => 'Super Administrator',
            'permissions' => '*', // tous les droits
        ],
        'admin' => [
            'name' => 'Administrator',
            'permissions' => [
                'create-users', 'edit-users', 'view-users',
                'manage-warehouses', 'view-all-warehouses',
                'manage-all-stocks', 'view-all-stocks',
                'view-all-orders', 'cancel-orders',
                'view-all-finances',
            ],
        ],
        'global_accountant' => [
            'name' => 'Global Accountant',
            'permissions' => [
                'view-all-warehouses', 'view-all-stocks',
                'view-all-orders', 'view-all-finances',
                'manage-all-finances', 'create-invoices',
            ],
        ],
        'warehouse_director' => [
            'name' => 'Warehouse Director',
            'permissions' => [
                'view-warehouse', 'manage-warehouse-stock',
                'view-warehouse-stock', 'view-warehouse-orders',
                'cancel-orders', 'manage-warehouse-finances',
                'view-warehouse-finances',
            ],
        ],
        'warehouse_accountant' => [
            'name' => 'Warehouse Accountant',
            'permissions' => [
                'view-warehouse', 'view-warehouse-stock',
                'view-warehouse-orders', 'view-warehouse-finances',
                'manage-warehouse-finances', 'create-invoices',
            ],
        ],
        'cashier' => [
            'name' => 'Cashier',
            'permissions' => [
                'create-orders', 'edit-orders',
                'view-warehouse-orders', 'view-warehouse-stock',
            ],
        ],
        'delivery_person' => [
            'name' => 'Delivery Person',
            'permissions' => [
                'view-warehouse-orders', 'update-delivery-status',
            ],
        ],
        'client' => [
            'name' => 'Client',
            'permissions' => [
                'create-orders', 'view-own-orders',
            ],
        ],
    ],
    'all_permissions' => [
        'create-users', 'edit-users', 'view-users', 'delete-users',
        'manage-warehouses', 'view-warehouse', 'view-all-warehouses',
        'manage-warehouse-stock', 'manage-all-stocks',
        'view-warehouse-stock', 'view-all-stocks',
        'create-orders', 'edit-orders', 'view-own-orders',
        'view-warehouse-orders', 'view-all-orders', 'cancel-orders',
        'update-delivery-status', 'view-warehouse-finances',
        'view-all-finances', 'manage-warehouse-finances',
        'manage-all-finances', 'create-invoices',
    ],
];
