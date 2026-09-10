# Shena Companion Welfare Association - Dynamic Website

A comprehensive web application for managing a welfare association that provides affordable funeral services and burial expense coverage to its members.

## Features

### Public Website
- **Homepage**: Overview of services, benefits, and call-to-action for registration
- **About Us**: Organization's mission, vision, and objectives
- **Membership**: Package options with pricing matrix and eligibility criteria
- **Services**: Detailed service offerings
- **Contact**: Contact form and information

### Member Portal
- **Dashboard**: Overview of member status, payments, and recent activity
- **Profile Management**: Update personal information and next of kin details
- **Payment History**: View all payment transactions and receipts
- **Beneficiary Management**: Add, edit, and manage beneficiaries
- **Claims Submission**: Submit and track insurance claims
- **Document Upload**: Upload required claim documents

### Administrative Backend
- **Member Management**: Complete member database with search and filtering
- **Payment Processing**: M-Pesa integration and payment tracking
- **Claims Processing**: Review, approve/reject claims with document verification
- **Communications**: Bulk email/SMS messaging system
- **Financial Reports**: Revenue, payment statistics, and analytics
- **System Settings**: Configure application settings and parameters

## Technical Stack

- **Backend**: PHP 8.0+ with custom MVC framework
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Payment Integration**: M-Pesa STK Push API (Safaricom)
- **SMS Service**: HostPinnacle API
- **Email Service**: SMTP (configurable)

## Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer (optional, for future dependencies)

### Setup Instructions

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   # OR download and extract the ZIP file
   ```

2. **Database Setup**
   ```bash
   # Create database and import schema
   mysql -u root -p
   CREATE DATABASE shena_welfare_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE shena_welfare_db;
   SOURCE database/schema.sql;
   ```

3. **Configuration**
   - Edit `config/config.php` and update the following:
     - Database credentials (DB_HOST, DB_NAME, DB_USER, DB_PASS)
     - M-Pesa API credentials
     - SMTP email settings
   - HostPinnacle SMS settings
     - APP_URL (your domain)

4. **Directory Permissions**
   ```bash
   # Ensure upload directory is writable
   chmod 755 storage/uploads
   ```

5. **Run Development Server**
   ```bash
   php -S localhost:8001
   ```
   Then visit: http://localhost:8001

6. **Apache Configuration** (For Production)
   - Ensure mod_rewrite is enabled
   - Point DocumentRoot to the project directory
   - The .htaccess file should handle URL routing

6. **Default Admin Account**
   - Email: 
   - Password: 


## Configuration

### Database Configuration
Update in `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'shena_welfare_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### M-Pesa Configuration
```php
define('MPESA_CONSUMER_KEY', 'your_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
define('MPESA_BUSINESS_SHORTCODE', '4163987');
define('MPESA_PASSKEY', 'your_passkey');
```

### Email Configuration (SMTP)
```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your_email@gmail.com');
define('MAIL_PASSWORD', 'your_app_password');
```

### HostPinnacle SMS Configuration
```php
define('HOSTPINNACLE_API_KEY', 'your_hostpinnacle_api_key');
define('HOSTPINNACLE_SENDER_ID', 'your_sender_id');
```

## File Structure

```
shena-welfare/
├── app/
│   ├── controllers/          # Application controllers
│   ├── models/              # Database models
│   ├── services/            # Business logic services
│   ├── core/                # Core framework classes
│   └── helpers/             # Helper functions
├── config/
│   └── config.php           # Application configuration
├── database/
│   └── schema.sql           # Database schema
├── resources/
│   └── views/               # View templates
├── storage/
│   └── uploads/             # File uploads
├── public/                  # Static assets (CSS, JS, images)
├── index.php                # Main entry point
├── .htaccess                # Apache rewrite rules
└── README.md
```

## Usage

### Member Registration
1. Visit `/register` to create a new member account
2. Fill in personal information and select membership package
3. Pay registration fee (KES 200) via M-Pesa
4. Wait for admin approval and account activation

### Monthly Contributions
Members can pay monthly contributions via:
- M-Pesa Paybill: 4163987 (Account: Member Number)
- Bank transfer (manual processing)
- Cash payments at office

### Claims Process
1. Member submits claim through dashboard
2. Upload required documents:
   - Copy of ID/Birth Certificate
   - Death Certificate
   - Chief's Letter
   - Mortuary Invoice
3. Admin reviews and processes claim
4. Member receives notification of approval/rejection

### Grace Periods
- Members under 80 years: 4 months grace period
- Members 80+ years: 5 months grace period
- Reactivation fee: KES 100 + outstanding dues

## API Endpoints

### Payment API
- `POST /api/payment/initiate` - Initiate M-Pesa payment
- `POST /api/mpesa/callback` - M-Pesa payment callback

### Request Format (Payment Initiation)
```json
{
    "member_id": 123,
    "amount": 500,
    "phone_number": "+254700000000",
    "payment_type": "monthly"
}
```

## Security Features

- CSRF protection on all forms
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session management with timeout
- File upload validation and restrictions
- Access control with role-based permissions

## Membership Packages

1. **Individual Package**
   - Base: KES 500/month
   - Age-based pricing multipliers
   - Personal coverage only

2. **Couple Package**
   - KES 800/month
   - 10% discount
   - Covers both spouses

3. **Family Package**
   - KES 1,200/month
   - 15% discount
   - Up to 6 family members

4. **Executive Package**
   - KES 2,000/month
   - Premium services
   - Priority processing

## Support

For technical support or questions:
- Email: support@shenacompanion.org
- Phone: +254 700 000 000

## License

This project is proprietary software developed for Shena Companion Welfare Association.

## Changelog

### Version 1.0.0
- Initial release
- Member registration and management
- Payment processing with M-Pesa
- Claims management system
- Administrative dashboard
- Email and SMS notifications

---

**Important Notes:**
1. Always backup the database before making changes
2. Test payment integrations in sandbox mode first
3. Regularly update passwords and API keys
4. Monitor system logs for errors and security issues
5. Keep the system updated with security patches
