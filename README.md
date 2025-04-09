# Complexe Tanger Boulevard - Residential Management System

A comprehensive web application for managing residential properties, payments, and maintenance requests for Complexe Tanger Boulevard.

## Features

- **Role-based Authentication System**:
  - Resident Portal
  - Manager Portal
  - Admin Portal

- **Resident Features**:
  - View property details
  - Track payment history
  - Monitor pending dues
  - Submit and track maintenance requests

- **Manager Features**:
  - Manage residents (add/edit/deactivate)
  - Assign properties
  - Record payments
  - Generate receipts
  - Manage maintenance work
  - View reports

- **Admin Features**:
  - All manager features
  - Dashboard with payment analytics
  - CRUD operations for managers

## Technology Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Hosting**: Compatible with cPanel (v126.0.11)

## Installation Instructions

### Prerequisites

- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache mod_rewrite enabled (for clean URLs)

### Setup Steps

1. **Clone or Download the Repository**

```bash
git clone [repository-url] CTB
cd CTB
```

2. **Create Database**

- Create a new MySQL database named `ctb_db` (or choose your own name)
- Import the database schema from `ctb_db.sql`

```bash
mysql -u [username] -p [database_name] < ctb_db.sql
```

3. **Configure Database Connection**

- Edit the `includes/config.php` file with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ctb_db'); // Your database name
define('DB_USER', 'your_username'); // Your database username
define('DB_PASS', 'your_password'); // Your database password
```

4. **Configure Site URL**

- Update the site URL in `includes/config.php`:

```php
$siteUrl = "http://yourdomain.com/CTB"; // Update with your domain and path
```

5. **Set File Permissions**

```bash
chmod 755 -R ./
chmod 777 -R ./uploads/
```

6. **Test Default Login Credentials**

- **Admin**:
  - Email: admin@ctb.com
  - Password: admin123

- **Manager**:
  - Email: manager@ctb.com
  - Password: manager123

- **Resident**:
  - Email: resident@ctb.com
  - Password: resident123

## Directory Structure

```
CTB/
├── admin/              # Admin portal files
├── manager/            # Manager portal files
├── resident/           # Resident portal files
├── includes/           # Shared PHP code & functions
├── uploads/            # File uploads (receipts, etc.)
├── css/                # Stylesheets
├── js/                 # JavaScript files
├── images/             # Image assets
├── index.php           # Main landing page
├── login.php           # Authentication portal
├── auth.php            # Authentication handler
├── logout.php          # Session termination script
├── ctb_db.sql          # Database schema & sample data
└── README.md           # Documentation
```

## Security Considerations

- All passwords are stored using PHP's `password_hash()` function
- Input validation and sanitization throughout the application
- Prepared statements for database queries to prevent SQL injection
- Role-based access control for all pages

## Support and Maintenance

For technical support or feature requests, please contact:

- Email: support@ctb.com
- Phone: [Your support phone number]

## License

[Your license information] 