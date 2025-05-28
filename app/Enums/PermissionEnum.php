<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self USERS_VIEW()
 * @method static self USERS_CREATE()
 * @method static self USERS_EDIT()
 * @method static self USERS_DELETE()
 * @method static self ROLES_VIEW()
 * @method static self ROLES_CREATE()
 * @method static self ROLES_EDIT()
 * @method static self ROLES_ASSIGN_PERMISSIONS()
 * @method static self DISTRIBUTION_CENTERS_VIEW()
 * @method static self DISTRIBUTION_CENTERS_CREATE()
 * @method static self DISTRIBUTION_CENTERS_EDIT()
 * @method static self DISTRIBUTION_CENTERS_DELETE()
 * @method static self DISTRIBUTION_CENTER_MANAGE_OWN()
 * @method static self DISTRIBUTION_CENTER_ASSIGN_DELIVERERS()
 * @method static self ORDERS_VIEW()
 * @method static self ORDERS_CREATE()
 * @method static self ORDERS_EDIT()
 * @method static self ORDERS_DELETE()
 * @method static self ORDERS_ASSIGN()
 * @method static self ORDERS_VIEW_OWN()
 * @method static self ORDERS_CREATE_OWN()
 * @method static self ORDERS_VIEW_ASSIGNED()
 * @method static self DELIVERIES_VIEW()
 * @method static self DELIVERIES_CREATE()
 * @method static self DELIVERIES_EDIT()
 * @method static self DELIVERIES_DELETE()
 * @method static self DELIVERIES_ASSIGN()
 * @method static self DELIVERIES_VIEW_OWN()
 * @method static self DELIVERIES_EDIT_OWN()
 * @method static self DELIVERIES_TRACK_OWN()
 * @method static self PRODUCTS_VIEW()
 * @method static self PRODUCTS_CREATE()
 * @method static self PRODUCTS_EDIT()
 * @method static self PRODUCTS_DELETE()
 * @method static self BOTTLES_TRACK()
 * @method static self BOTTLES_SCAN()
 * @method static self BOTTLES_ASSIGN()
 * @method static self BOTTLE_TYPES_VIEW()
 * @method static self BOTTLE_TYPES_CREATE()
 * @method static self BOTTLE_TYPES_EDIT()
 * @method static self BOTTLE_TYPES_DELETE()
 * @method static self ACCESSORIES_VIEW()
 * @method static self ACCESSORIES_CREATE()
 * @method static self ACCESSORIES_EDIT()
 * @method static self ACCESSORIES_DELETE()
 * @method static self ACCESSORY_TYPES_VIEW()
 * @method static self ACCESSORY_TYPES_CREATE()
 * @method static self ACCESSORY_TYPES_EDIT()
 * @method static self ACCESSORY_TYPES_DELETE()
 * @method static self SUPPLIERS_VIEW()
 * @method static self SUPPLIERS_CREATE()
 * @method static self SUPPLIERS_EDIT()
 * @method static self SUPPLIERS_DELETE()
 * @method static self SUPPLIER_DELIVERIES_VIEW()
 * @method static self SUPPLIER_DELIVERIES_CREATE()
 * @method static self SUPPLIER_DELIVERIES_EDIT()
 * @method static self SUPPLIER_DELIVERIES_DELETE()
 * @method static self CUSTOMERS_VIEW()
 * @method static self CUSTOMERS_CREATE()
 * @method static self CUSTOMERS_EDIT()
 * @method static self CUSTOMERS_DELETE()
 * @method static self PAYMENTS_VIEW()
 * @method static self PAYMENTS_EDIT()
 * @method static self REPORTS_MANAGE()
 * @method static self GAS_MANAGE()
 * @method static self COMMENTS_VIEW()
 * @method static self COMMENTS_MODERATE()
 * @method static self PROFILE_EDIT()
 * @method static self HISTORY_VIEW_OWN()
 * @method static self MOBILE_ACCESS()
 * @method static self DELIVERY_TRACK_LOCATION()
 */
class PermissionEnum extends Enum
{
    public static function values(): array
    {
        return [
            // USER MANAGEMENT
            'USERS_VIEW' => 'users.view',
            'USERS_CREATE' => 'users.create',
            'USERS_EDIT' => 'users.edit',
            'USERS_DELETE' => 'users.delete',

            // ROLE MANAGEMENT
            'ROLES_VIEW' => 'roles.view',
            'ROLES_CREATE' => 'roles.create',
            'ROLES_EDIT' => 'roles.edit',
            'ROLES_ASSIGN_PERMISSIONS' => 'roles.assign_permissions',

            // DISTRIBUTION CENTERS
            'DISTRIBUTION_CENTERS_VIEW' => 'distribution_centers.view',
            'DISTRIBUTION_CENTERS_CREATE' => 'distribution_centers.create',
            'DISTRIBUTION_CENTERS_EDIT' => 'distribution_centers.edit',
            'DISTRIBUTION_CENTERS_DELETE' => 'distribution_centers.delete',
            'DISTRIBUTION_CENTER_MANAGE_OWN' => 'distribution_center.manage_own',
            'DISTRIBUTION_CENTER_ASSIGN_DELIVERERS' => 'distribution_center.assign_deliverers',

            // ORDERS
            'ORDERS_VIEW' => 'orders.view',
            'ORDERS_CREATE' => 'orders.create',
            'ORDERS_EDIT' => 'orders.edit',
            'ORDERS_DELETE' => 'orders.delete',
            'ORDERS_ASSIGN' => 'orders.assign',
            'ORDERS_VIEW_OWN' => 'orders.view_own',
            'ORDERS_CREATE_OWN' => 'orders.create_own',
            'ORDERS_VIEW_ASSIGNED' => 'orders.view_assigned',

            // DELIVERIES
            'DELIVERIES_VIEW' => 'deliveries.view',
            'DELIVERIES_CREATE' => 'deliveries.create',
            'DELIVERIES_EDIT' => 'deliveries.edit',
            'DELIVERIES_DELETE' => 'deliveries.delete',
            'DELIVERIES_ASSIGN' => 'deliveries.assign',
            'DELIVERIES_VIEW_OWN' => 'deliveries.view_own',
            'DELIVERIES_EDIT_OWN' => 'deliveries.edit_own',
            'DELIVERIES_TRACK_OWN' => 'deliveries.track_own',

            // BOTTLES
            'PRODUCTS_VIEW' => 'products.view',
            'PRODUCTS_CREATE' => 'products.create',
            'PRODUCTS_EDIT' => 'products.edit',
            'PRODUCTS_DELETE' => 'products.delete',
            'BOTTLES_TRACK' => 'bottles.track',
            'BOTTLES_SCAN' => 'bottles.scan',
            'BOTTLES_ASSIGN' => 'bottles.assign',

            // BOTTLE TYPES
            'BOTTLE_TYPES_VIEW' => 'bottle_types.view',
            'BOTTLE_TYPES_CREATE' => 'bottle_types.create',
            'BOTTLE_TYPES_EDIT' => 'bottle_types.edit',
            'BOTTLE_TYPES_DELETE' => 'bottle_types.delete',

            // ACCESSORIES
            'ACCESSORIES_VIEW' => 'accessories.view',
            'ACCESSORIES_CREATE' => 'accessories.create',
            'ACCESSORIES_EDIT' => 'accessories.edit',
            'ACCESSORIES_DELETE' => 'accessories.delete',

            // ACCESSORY TYPES
            'ACCESSORY_TYPES_VIEW' => 'accessory_types.view',
            'ACCESSORY_TYPES_CREATE' => 'accessory_types.create',
            'ACCESSORY_TYPES_EDIT' => 'accessory_types.edit',
            'ACCESSORY_TYPES_DELETE' => 'accessory_types.delete',

            // SUPPLIERS
            'SUPPLIERS_VIEW' => 'suppliers.view',
            'SUPPLIERS_CREATE' => 'suppliers.create',
            'SUPPLIERS_EDIT' => 'suppliers.edit',
            'SUPPLIERS_DELETE' => 'suppliers.delete',
            'SUPPLIER_DELIVERIES_VIEW' => 'supplier_deliveries.view',
            'SUPPLIER_DELIVERIES_CREATE' => 'supplier_deliveries.create',
            'SUPPLIER_DELIVERIES_EDIT' => 'supplier_deliveries.edit',
            'SUPPLIER_DELIVERIES_DELETE' => 'supplier_deliveries.delete',

            // CUSTOMERS
            'CUSTOMERS_VIEW' => 'customers.view',
            'CUSTOMERS_CREATE' => 'customers.create',
            'CUSTOMERS_EDIT' => 'customers.edit',
            'CUSTOMERS_DELETE' => 'customers.delete',

            // PAYMENTS
            'PAYMENTS_VIEW' => 'payments.view',
            'PAYMENTS_EDIT' => 'payments.edit',

            // REPORTS AND ANALYTICS
            'REPORTS_MANAGE' => 'reports.manage',

            // GAS MANAGEMENT
            'GAS_MANAGE' => 'gas.manage',

            // COMMENTS
            'COMMENTS_VIEW' => 'comments.view',
            'COMMENTS_MODERATE' => 'comments.moderate',

            // PROFILE
            'PROFILE_EDIT' => 'profile.edit',

            // HISTORY
            'HISTORY_VIEW_OWN' => 'history.view_own',

            // MOBILE ACCESS
            'MOBILE_ACCESS' => 'mobile.access',

            // GEOLOCATION
            'DELIVERY_TRACK_LOCATION' => 'delivery.track_location',
        ];
    }
}
