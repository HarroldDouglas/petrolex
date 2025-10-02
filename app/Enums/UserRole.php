<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self SUPER_ADMIN()
 * @method static self ADMIN()
 * @method static self MANAGER()
 * @method static self ACCOUNTANT()
 * @method static self GAS_MANAGER()
 * @method static self CENTER_MANAGER()
 * @method static self DELIVERY_PERSON()
 * @method static self CUSTOMER()
 */
class UserRole extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'SUPER_ADMIN' => 'Super Administrateur',
            'ADMIN' => 'Administrateur',
            'MANAGER' => 'Gestionnaire',
            'ACCOUNTANT' => 'Comptable',
            'GAS_MANAGER' => 'Gestionnaire de Gaz',
            'CENTER_MANAGER' => 'Responsable de Centre',
            'DELIVERY_PERSON' => 'Livreur',
            'CUSTOMER' => 'Client',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'SUPER_ADMIN' => 'super_admin',
            'ADMIN' => 'admin',
            'MANAGER' => 'manager',
            'ACCOUNTANT' => 'accountant',
            'GAS_MANAGER' => 'gas_manager',
            'CENTER_MANAGER' => 'center_manager',
            'DELIVERY_PERSON' => 'delivery_person',
            'CUSTOMER' => 'customer',
        ];
    }

    /**
     * Get permissions for this role
     */
    public function permissions(): array
    {
        $allPermissions = PermissionEnum::values();

        return match ($this->value) {
            'super_admin' => $allPermissions,
            'admin' => array_filter($allPermissions, function ($permission) {
                return ! in_array($permission, [
                    PermissionEnum::ROLES_CREATE()->value,
                    PermissionEnum::ROLES_EDIT()->value,
                    PermissionEnum::ROLES_MANAGE()->value,
                    PermissionEnum::SUPPLIER_DELIVERIES_CREATE()->value,
                    PermissionEnum::SUPPLIER_DELIVERIES_EDIT()->value,
                ]);
            }),
            'manager' => array_filter($allPermissions, function ($permission) {
                $restrictedPermissions = [
                    PermissionEnum::USERS_CREATE()->value,
                    PermissionEnum::USERS_EDIT()->value,
                    PermissionEnum::USERS_DELETE()->value,
                    PermissionEnum::ROLES_VIEW()->value,
                    PermissionEnum::ROLES_CREATE()->value,
                    PermissionEnum::ROLES_EDIT()->value,
                    PermissionEnum::ROLES_MANAGE()->value,
                    PermissionEnum::DISTRIBUTION_CENTERS_CREATE()->value,
                    PermissionEnum::DISTRIBUTION_CENTERS_EDIT()->value,
                    PermissionEnum::DISTRIBUTION_CENTERS_DELETE()->value,
                    PermissionEnum::SUPPLIER_DELIVERIES_CREATE()->value,
                    PermissionEnum::SUPPLIER_DELIVERIES_EDIT()->value,
                ];

                return ! in_array($permission, $restrictedPermissions);
            }),
            'accountant', 'gas_manager' => [
                PermissionEnum::USERS_VIEW()->value,
                PermissionEnum::ROLES_VIEW()->value,
                PermissionEnum::DISTRIBUTION_CENTERS_VIEW()->value,
                PermissionEnum::ORDERS_VIEW()->value,
                PermissionEnum::DELIVERIES_VIEW()->value,
                PermissionEnum::PRODUCTS_VIEW()->value,
                PermissionEnum::CUSTOMERS_VIEW()->value,
                PermissionEnum::PAYMENTS_MANAGE()->value,
                PermissionEnum::REPORTS_MANAGE()->value,
                PermissionEnum::GAS_MANAGE()->value,
            ],
            'center_manager' => [
                // Distribution Center manager
                PermissionEnum::DISTRIBUTION_CENTER_MANAGE_OWN()->value,
                PermissionEnum::DISTRIBUTION_CENTER_ASSIGN_DELIVERERS()->value,
                // Permissions on orders
                PermissionEnum::ORDERS_VIEW()->value,
                PermissionEnum::ORDERS_CREATE()->value,
                PermissionEnum::ORDERS_EDIT()->value,
                PermissionEnum::ORDERS_ASSIGN()->value,
                // Permissions on Users
                PermissionEnum::USERS_VIEW()->value,
                // Permissions on deliveries
                PermissionEnum::DELIVERIES_VIEW()->value,
                PermissionEnum::DELIVERIES_CREATE()->value,
                PermissionEnum::DELIVERIES_EDIT()->value,
                PermissionEnum::DELIVERIES_ASSIGN()->value,
                // Permissions on supplier deliveries
                PermissionEnum::SUPPLIER_DELIVERIES_VIEW()->value,
                PermissionEnum::SUPPLIER_DELIVERIES_CREATE()->value,
                PermissionEnum::SUPPLIER_DELIVERIES_EDIT()->value,
                // Permissions on customers
                PermissionEnum::CUSTOMERS_VIEW()->value,
                // Reports
                PermissionEnum::REPORTS_MANAGE()->value,
            ],
            'delivery_person' => [
                PermissionEnum::ORDERS_VIEW_ASSIGNED()->value,
                PermissionEnum::DELIVERIES_VIEW_OWN()->value,
                PermissionEnum::DELIVERIES_EDIT_OWN()->value,
                PermissionEnum::DELIVERIES_TRACK_OWN()->value,
                PermissionEnum::MOBILE_ACCESS()->value,
                PermissionEnum::DELIVERY_TRACK_LOCATION()->value,
            ],
            'customer' => [
                PermissionEnum::ORDERS_VIEW_OWN()->value,
                PermissionEnum::ORDERS_CREATE_OWN()->value,
                PermissionEnum::PROFILE_EDIT()->value,
                PermissionEnum::HISTORY_VIEW_OWN()->value,
                PermissionEnum::MOBILE_ACCESS()->value,
            ],
            default => [],
        };
    }
}
