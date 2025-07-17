<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self USERS_VIEW()
 * @method static self USERS_CREATE()
 * @method static self USERS_EDIT()
 * @method static self USERS_DELETE()
 * @method static self ROLES_MANAGE()
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
 * @method static self SUPPLIER_DELIVERIES_VIEW()
 * @method static self SUPPLIER_DELIVERIES_CREATE()
 * @method static self SUPPLIER_DELIVERIES_EDIT()
 * @method static self SUPPLIER_DELIVERIES_DELETE()
 * @method static self CUSTOMERS_VIEW()
 * @method static self PAYMENTS_MANAGE()
 * @method static self REPORTS_MANAGE()
 * @method static self GAS_MANAGE()
 * @method static self COMMENTS_VIEW()
 * @method static self COMMENTS_MANAGE()
 * @method static self MOBILE_ACCESS()
 * @method static self PROFILE_EDIT()
 * @method static self HISTORY_VIEW_OWN()
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
            'ROLES_MANAGE' => 'roles.manage',

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

            // PRODUCTS
            'PRODUCTS_VIEW' => 'products.view',
            'PRODUCTS_CREATE' => 'products.create',
            'PRODUCTS_EDIT' => 'products.edit',
            'PRODUCTS_DELETE' => 'products.delete',

            // SUPPLIERS DELIVERIES
            'SUPPLIER_DELIVERIES_VIEW' => 'supplier_deliveries.view',
            'SUPPLIER_DELIVERIES_CREATE' => 'supplier_deliveries.create',
            'SUPPLIER_DELIVERIES_EDIT' => 'supplier_deliveries.edit',
            'SUPPLIER_DELIVERIES_DELETE' => 'supplier_deliveries.delete',

            // CUSTOMERS
            'CUSTOMERS_VIEW' => 'customers.view',

            // PAYMENTS
            'PAYMENTS_MANAGE' => 'payments.manage',

            // REPORTS AND ANALYTICS
            'REPORTS_MANAGE' => 'reports.manage',

            // GAS MANAGEMENT
            'GAS_MANAGE' => 'gas.manage',

            // COMMENTS
            'COMMENTS_VIEW' => 'comments.view',
            'COMMENTS_MANAGE' => 'comments.manage',

            // MOBILE ACCESS
            'MOBILE_ACCESS' => 'mobile.access',
            'DELIVERY_TRACK_LOCATION' => 'delivery.track_location',
            'PROFILE_EDIT' => 'profile.edit',
            'HISTORY_VIEW_OWN' => 'history.view_own',

            // MUNICIPALITIES
            'MUNICIPALITIES_VIEW' => 'municipalities.view',
            'MUNICIPALITIES_CREATE' => 'municipalities.create',
        ];
    }
}
