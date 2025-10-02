# User Permission Management Feature

## Overview

This feature allows managing individual user permissions with a sophisticated system that handles:
- **Role-inherited permissions** (displayed with opacity)
- **Direct user permissions** (displayed normally)
- **Explicitly revoked permissions** (displayed with cross, override role permissions)
- **Unassigned permissions** (displayed as unchecked)

## Architecture

### Database Schema

#### `user_revoked_permissions` table
```sql
- id (primary key)
- user_id (foreign key to users)
- permission_id (foreign key to permissions) 
- created_at, updated_at
- unique constraint on (user_id, permission_id)
```

### Core Components

#### 1. `ManagesUserPermissions` Trait
Location: `app/Traits/ManagesUserPermissions.php`

Key methods:
- `revokedPermissions()`: Relationship to revoked permissions
- `revokeSpecificPermission()`: Revoke a permission (override role)
- `restoreRevokedPermission()`: Restore a revoked permission
- `getEffectivePermissions()`: Get final permissions (role + direct - revoked)
- `hasEffectivePermission()`: Check if user has effective permission
- `getPermissionsBySource()`: Get permissions grouped by source
- `getPermissionState()`: Determine UI state for a permission
- `canHavePermissionsManaged()`: Check if user can have permissions managed

#### 2. `UserPermissionService`
Location: `app/Services/User/UserPermissionService.php`

Handles business logic for:
- Getting user permission states for UI display
- Toggling permissions with proper state transitions
- Determining effective permissions

#### 3. Livewire Component: `ManageUserPermissions`
Location: `app/Livewire/User/ManageUserPermissions.php`

Interactive component for managing permissions with real-time updates.

#### 4. Controller: `ManageUserPermissionsController`
Location: `app/Http/Controllers/User/ManageUserPermissionsController.php`

### Permission Logic

#### Permission States:
1. **`role`**: Permission inherited from user's role (displayed with opacity)
2. **`direct`**: Permission granted directly to user (normal display)
3. **`revoked`**: Permission explicitly denied (displayed with cross)
4. **`none`**: Permission not assigned (unchecked)

#### State Transitions:
- **Click on `role` permission** → Becomes `revoked` (denied)
- **Click on `direct` permission** → Becomes `none` (removed)
- **Click on `revoked` permission** → Becomes `none` or `role` (restored)
- **Click on `none` permission** → Becomes `direct` (granted)

#### Final Permission Calculation:
```
Effective Permissions = (Role Permissions + Direct Permissions) - Revoked Permissions
```

### UI Features

#### Permission Display:
- **Role permissions**: 60% opacity, "Rôle" badge
- **Direct permissions**: Normal display, "Direct" badge  
- **Revoked permissions**: Strikethrough text, "✗ Révoqué" badge
- **Unassigned**: Unchecked, "Non assigné" badge

#### Access Control:
- Manage permissions button only shown for non-customer, non-delivery-person users
- Uses `canHavePermissionsManaged()` method

### Routes

```php
Route::get('/users/{user}/manage-permissions', ManageUserPermissionsController::class)
    ->name('users.manage-permissions');
```

### Usage Examples

#### In Blade Templates:
```php
@if($user->canHavePermissionsManaged())
    <a href="{{ route('users.manage-permissions', $user) }}">
        Manage Permissions
    </a>
@endif
```

#### Checking Effective Permissions:
```php
// Check if user has effective permission (considering revoked)
if ($user->hasEffectivePermission('users.view')) {
    // User can view users
}

// Get all effective permissions
$permissions = $user->getEffectivePermissions();
```

#### Programmatic Permission Management:
```php
// Revoke a role permission
$user->revokeSpecificPermission('users.edit');

// Grant direct permission
$user->givePermissionTo('reports.view');

// Restore revoked permission
$user->restoreRevokedPermission('users.edit');
```

### Testing

#### Artisan Command:
```bash
php artisan test:user-permissions [user_id]
```

#### Test Routes:
```
GET /test/user/{user}/permissions - View permission states
POST /test/user/{user}/permissions/{permission}/toggle - Toggle permission
```

### Business Rules

1. **Role Changes**: When user's role changes, direct and revoked permissions are preserved
2. **Security**: Revoked permissions take highest priority (cannot be overridden by role)
3. **Access Control**: Only non-customer, non-delivery-person users can have permissions managed
4. **UI Logic**: Permission states determine checkbox appearance and behavior

### Migration

Run migration to create the revoked permissions table:
```bash
php artisan migrate
```

The migration creates the `user_revoked_permissions` table with proper foreign key constraints and indexes.