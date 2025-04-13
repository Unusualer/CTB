# Role-Based Access Control (RBAC) System

This document outlines the role-based access control system implemented in the CTB application.

## Overview

The RBAC system controls access to different parts of the application based on user roles. There are three main roles in the system:

1. **Admin** - Full access to all features and functionality
2. **Manager** - Access to property and resident management features
3. **Resident** - Limited access to personal information and ticket submission

## Implementation

The RBAC system uses a combination of:

- **Existing functions in `includes/config.php`** - Base authentication functions
- **Extensions in `includes/role_access.php`** - Additional role-checking functions
- **Dynamic sidebar in `includes/dynamic-sidebar.php`** - Role-based navigation

## Key Functions

### From config.php (pre-existing):
- `isLoggedIn()` - Checks if a user is logged in
- `hasRole($role)` - Checks if the logged-in user has a specific role
- `isAdmin()`, `isManager()`, `isResident()` - Helper functions for checking specific roles
- `requireRole($role)` - Requires the user to have a specific role, redirects if not
- `requireLogin()` - Requires the user to be logged in, redirects if not

### From role_access.php (new extensions):
- `hasAnyRole($roles)` - Checks if the user has at least one of the specified roles
- `requireAnyRole($roles)` - Requires the user to have at least one of the specified roles
- `getCurrentRole()` - Gets the current user's role from either 'role' or 'user_role' session variable

## Usage Example

```php
// Include the required files
require_once '../includes/config.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has admin role
requireRole('admin');

// OR: Allow either admin or manager
requireAnyRole(['admin', 'manager']);

// Check role in conditional logic
if (isAdmin()) {
    // Show admin-only content
}
```

## Directory Access

- The `/admin` directory is restricted to users with the 'admin' role
- The `/manager` directory is restricted to users with the 'manager' role
- The `/resident` directory is restricted to users with the 'resident' role

Each directory has been updated to enforce these role requirements.

## Dynamic Navigation

The dynamic sidebar automatically shows menu items based on the user's role:

```php
<?php include '../includes/dynamic-sidebar.php'; ?>
```

## Session Variables

The RBAC system works with either of these session variable combinations:

- `$_SESSION['user_id']` and `$_SESSION['role']`
- `$_SESSION['user_id']` and `$_SESSION['user_role']`

## Extending the System

To add new roles or permissions:

1. Update the database schema to include the new role
2. Add role checking methods in config.php if needed
3. Update dynamic-sidebar.php to include menu items for the new role

## Troubleshooting

If users are being redirected unexpectedly, check:

1. That the session is started (`session_start()`) before including the RBAC files
2. That the correct role requirement is being enforced
3. That the user's role is being properly set during login (check both 'role' and 'user_role' session variables) 