# Community Trust Bank (CTB) Role-Based Access Control

This document provides a comprehensive guide to the role-based access control (RBAC) system implemented in the CTB application.

## Overview

The RBAC system controls user access to various parts of the application based on their assigned roles. The system defines three primary roles:

1. **Admin** - Full access to all features and functionality
2. **Manager** - Access to property and resident management features
3. **Resident** - Limited access to personal information and ticket submission

## Implementation Details

### Core Files

- `includes/role_access.php` - Contains the core RBAC functions
- `includes/dynamic-sidebar.php` - Provides a role-based navigation menu
- `auth.php` - Handles user authentication and role-based redirects

### Access Control Functions

The following functions are available in `includes/role_access.php`:

```php
// Check if user is logged in
bool isLoggedIn()

// Check if user has a specific role
bool hasRole(string $role)

// Convenience functions for specific roles
bool isAdmin()
bool isManager()
bool isResident()

// Check if user has any of the specified roles
bool hasAnyRole(array $roles)

// Require functions that redirect if conditions aren't met
void requireLogin(string $redirect_url = '../login.php')
void requireRole(string $role, string $redirect_url = '../login.php')
void requireAnyRole(array $roles, string $redirect_url = '../login.php')
```

### Directory Structure

Each role has its own directory with access restricted to users with that role:

- `/admin` - Admin-only access
- `/manager` - Manager-only access
- `/resident` - Resident-only access

## Usage Examples

### Restricting Page Access

To restrict access to a page based on role:

```php
<?php
// At the top of your PHP file
require_once '../includes/role_access.php';

// Require admin role
requireRole('admin');

// Or allow either admin or manager
requireAnyRole(['admin', 'manager']);
```

### Conditionally Displaying Content

To show/hide content based on role:

```php
<?php if (isAdmin()): ?>
    <!-- Admin-only content -->
    <div class="admin-panel">
        <h2>Administrative Controls</h2>
        <!-- ... -->
    </div>
<?php endif; ?>

<?php if (hasAnyRole(['admin', 'manager'])): ?>
    <!-- Content for both admin and manager -->
    <div class="management-tools">
        <!-- ... -->
    </div>
<?php endif; ?>
```

### Using the Dynamic Sidebar

To use the dynamic sidebar that shows role-appropriate menu items:

```php
<!-- In your layout file -->
<?php include '../includes/dynamic-sidebar.php'; ?>
```

## Authentication Flow

1. User logs in via `login.php` which submits to `auth.php`
2. `auth.php` verifies credentials and sets role in session
3. User is redirected to the appropriate dashboard based on role
4. RBAC functions in each page ensure the user has the proper role

## Session Variables

The RBAC system relies on these session variables:

- `$_SESSION['user_id']` - User's ID
- `$_SESSION['role']` - User's role (admin, manager, resident)
- `$_SESSION['name']` - User's display name

## Extending the System

### Adding a New Role

1. Update the database schema to include the new role
2. Add a corresponding function in `role_access.php` (e.g., `isNewRole()`)
3. Update `dynamic-sidebar.php` to include menu items for the new role
4. Create a directory for the new role if needed

### Adding Granular Permissions

For more granular permissions within roles:

1. Create a permissions table in the database
2. Extend `role_access.php` with permission-checking functions
3. Add permission checks to relevant pages

## Troubleshooting

### Common Issues

- **Unexpected redirects**: Check that session variables are properly set
- **Missing menu items**: Ensure role is correctly assigned and stored in session
- **Access denied errors**: Verify role requirements match user's actual role

### Debugging Tips

1. Check session variables: `var_dump($_SESSION);`
2. Verify user's role in the database
3. Ensure `role_access.php` is included before use

## Security Considerations

- Session data is critical for RBAC; ensure session security is maintained
- Protect against session fixation and hijacking attacks
- Always validate roles server-side, never rely on client-side checks alone
- Use HTTPS to protect session cookies in transit

## Conclusion

This RBAC system provides a secure and flexible way to control access to different parts of the application based on user roles. By following the patterns established in this system, you can easily extend and maintain role-based security throughout the application. 