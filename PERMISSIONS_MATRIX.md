# CTB System - Role-Based Permissions Matrix

## Overview
This document outlines the CRUD (Create, Read, Update, Delete) permissions for each user role in the CTB system.

---

## ADMIN - Full Access

### Users Management
- **Create**: ✅ All users (admin, manager, resident)
- **Read**: ✅ All users
- **Update**: ✅ All users
- **Delete**: ✅ All users (except themselves)

### Properties Management
- **Create**: ✅ All properties (apartments, parking spaces)
- **Read**: ✅ All properties
- **Update**: ✅ All properties (assign/unassign to residents)
- **Delete**: ✅ All properties

### Tickets Management
- **Create**: ✅ Tickets for any user
- **Read**: ✅ All tickets
- **Update**: ✅ All tickets (status, priority, response)
- **Delete**: ✅ All tickets

### Payments Management
- **Create**: ✅ All payments
- **Read**: ✅ All payments
- **Update**: ✅ All payments (status, amount, etc.)
- **Delete**: ✅ All payments

### Maintenance Management
- **Create**: ✅ All maintenance requests
- **Read**: ✅ All maintenance requests
- **Update**: ✅ All maintenance requests (status, priority, etc.)
- **Delete**: ✅ All maintenance requests

### Activity Log
- **Read**: ✅ All activity logs
- **Export**: ✅ Activity logs

### Dashboard
- **Access**: ✅ Full dashboard with all statistics

---

## MANAGER - Limited Administrative Access

### Users Management
- **Create**: ✅ Residents only
- **Read**: ✅ Residents only
- **Update**: ✅ Residents only 
- **Delete**: ❌ Cannot delete users

### Properties Management
- **Create**: ✅ All properties
- **Read**: ✅ All properties
- **Update**: ✅ All properties (assign/unassign to residents)
- **Delete**: ❌ Cannot delete properties

### Tickets Management
- **Create**: ✅ Tickets (can create on behalf of residents)
- **Read**: ✅ All tickets
- **Update**: ✅ All tickets (respond, change status, priority)
- **Delete**: ❌ Cannot delete tickets

### Payments Management
- **Create**: ✅ Payments for residents
- **Read**: ✅ All payments
- **Update**: ✅ Payments (mark as paid, update status)
- **Delete**: ❌ Cannot delete payments

### Maintenance Management
- **Create**: ✅ Maintenance requests
- **Read**: ✅ All maintenance requests
- **Update**: ✅ All maintenance requests (status, priority, assign contractors)
- **Delete**: ❌ Cannot delete maintenance requests

### Activity Log
- **Read**: ✅ View activity logs (filtered to relevant actions)
- **Export**: ❌ Cannot export

### Dashboard
- **Access**: ✅ Dashboard with manager-relevant statistics 

---

## RESIDENT - Self-Service Access

### Users Management
- **Create**: ❌ Cannot create users
- **Read**: ✅ Own profile only
- **Update**: ✅ Own profile only (name, email, phone, password)
- **Delete**: ❌ Cannot delete users

### Properties Management
- **Create**: ❌ Cannot create properties
- **Read**: ✅ Own properties only
- **Update**: ❌ Cannot update properties
- **Delete**: ❌ Cannot delete properties

### Tickets Management
- **Create**: ✅ Own tickets only
- **Read**: ✅ Own tickets only
- **Update**: ✅ Own tickets only (can update description, cannot change status/priority)
- **Delete**: ❌ Cannot delete tickets

### Payments Management
- **Create**: ❌ Cannot create payments
- **Read**: ✅ Own payments only
- **Update**: ❌ Cannot update payments
- **Delete**: ❌ Cannot delete payments

### Maintenance Management
- **Create**: ✅ Own maintenance requests
- **Read**: ✅ Own maintenance requests
- **Update**: ✅ Own maintenance requests (can update description, cannot change status)
- **Delete**: ❌ Cannot delete maintenance requests

### Activity Log
- **Read**: ✅ Own activity log only
- **Export**: ❌ Cannot export

### Dashboard
- **Access**: ✅ Personal dashboard with own statistics only

---

## Implementation Notes

1. **Role Checking**: All files use `requireRole('admin')` - this needs to be updated to `requireAnyRole(['admin', 'manager'])` or `requireAnyRole(['admin', 'manager', 'resident'])` based on the permission level.

2. **Data Filtering**: 
   - Manager views should filter out admin users from user lists
   - Resident views should filter all data to show only their own records

3. **UI Elements**: 
   - Hide/disable buttons for actions the user cannot perform
   - Show appropriate error messages when unauthorized actions are attempted

4. **Database Queries**: 
   - Add WHERE clauses to filter data based on user role
   - For residents: `WHERE user_id = ?` or `WHERE property_id IN (SELECT id FROM properties WHERE user_id = ?)`

---

## Recommended Permission Changes by File

### Files that should allow MANAGER access:
- `properties.php` - Read/Update (no delete)
- `tickets.php` - Full CRUD (no delete)
- `payments.php` - Read/Update (no create/delete)
- `maintenance.php` - Full CRUD (no delete)
- `dashboard.php` - View with filtered data
- `activity-log.php` - Read only

### Files that should allow RESIDENT access:
- `view-user.php` - Own profile only
- `edit-user.php` - Own profile only
- `view-property.php` - Own properties only
- `tickets.php` - Own tickets only
- `add-ticket.php` - Create own tickets
- `edit-ticket.php` - Edit own tickets (limited fields)
- `view-payment.php` - Own payments only
- `view-maintenance.php` - Own maintenance only
- `add-maintenance.php` - Create own maintenance
- `edit-maintenance.php` - Edit own maintenance (limited fields)
- `dashboard.php` - Personal dashboard only
- `activity-log.php` - Own activity only

### Files that should remain ADMIN only:
- `users.php` - Full user management
- `add-user.php` - Create users (manager can create residents)
- `edit-user.php` - Edit users (manager can edit residents)
- `delete-user.php` - Delete users
- `add-property.php` - Create properties
- `edit-property.php` - Edit properties
- `delete-payment.php` - Delete payments
- `delete-maintenance.php` - Delete maintenance
- `export-activity-log.php` - Export logs

