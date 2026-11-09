# Repair System

A web-based repair service management system with comprehensive user management and security features.

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/CodeWithErza/RepairSystem.git
cd RepairSystem
```

### 2. Install Dependencies

```bash
composer install
```

This will:

- Install required packages (including PHPMailer for email notifications)
- Set up autoloading
- Create initial configuration files

### 3. Database Setup

1. Create a MySQL database named 'repairsystem'
2. Import the main database schema:
   ```bash
   mysql -u root repairsystem < database/repairsystem.sql
   ```
3. Apply the password reset feature migration:
   ```bash
   mysql -u root repairsystem < database/migrations/add_reset_token_fields.sql
   ```
4. Configure database connection in `config/database.php`:
   ```php
   return [
       'host' => 'localhost',
       'dbname' => 'repairsystem',
       'username' => 'root',
       'password' => ''
   ];
   ```

### 4. Email Configuration (Required for Password Reset)

1. Create a Gmail account or use an existing one
2. Set up 2-Factor Authentication in your Gmail account
3. Generate an App Password:

   - Go to Google Account Settings > Security
   - Under 2-Step Verification, click on 'App passwords'
   - Select 'Other' as app and give it a name (e.g., 'RepairSystem')
   - Copy the generated 16-character password

4. Set up mail configuration:

   - Copy the example config: `cp config/mail.example.php config/mail.php`
   - Edit `config/mail.php` with your SMTP credentials:

   ```php
   return [
       'smtp' => [
           'host' => 'smtp.gmail.com',
           'port' => 587,
           'username' => 'your-email@gmail.com',
           'password' => 'your-16-char-app-password',
           'from_email' => 'your-email@gmail.com',
           'from_name' => 'Repair System'
       ],
       'options' => [
           'base_url' => 'http://localhost/RepairSystem',
           'token_expiry' => 3600,
           'reset_link_expiry' => '+1 hour'
       ]
   ];
   ```

   > ⚠️ IMPORTANT: Security Notes
   >
   > - The `config/mail.php` file contains sensitive information and is excluded from git
   > - Never commit your actual mail.php file
   > - Each team member should maintain their own mail.php file locally
   > - Use mail.example.php as a template for configuration

## Features

### Authentication & Security

- Secure login system with password hashing
- Password reset functionality with email verification
- Token-based password reset with 1-hour expiry
- Session management with automatic cleanup
- Protection against brute force attacks

### Staff Management

- Staff registration and profile management
- Role-based access control
- Activity logging and last login tracking
- Password change functionality

### Business Operations

- Customer Management
- Service Reports
- Parts Inventory
- Transaction Tracking

### Email Notifications

- Password reset links
- Account notifications
- Service status updates

## Directory Structure

```
RepairSystem/
├── authentication/    # Authentication related files
├── backend/          # Backend logic and handlers
│   ├── api/          # API endpoints
│   └── handlers/     # Business logic handlers
├── config/           # Configuration files
├── database/         # Database schema and migrations
├── layout/           # Common layout files
├── views/            # Frontend views
└── vendor/           # Composer dependencies
```

## Testing the Password Reset Feature

1. Click 'Forgot Password' on the login page
2. Enter your email address
3. Check your email for the reset link
4. Click the link (valid for 1 hour)
5. Enter your new password

## Troubleshooting

### Email Issues

- Verify SMTP credentials in `config/mail.php`
- Check if App Password is correct
- Ensure email address matches Gmail account
- Check spam folder for reset emails

### Database Issues

- Verify database connection settings
- Ensure reset token fields are added to staff table
- Check if MySQL server is running
- Verify table permissions

### Token Expiry Issues

- Check server timezone settings
- Verify system time is correct
- Ensure reset_token_expiry field is TIMESTAMP

## Security Notes

- Never commit `config/mail.php` as it contains sensitive information
- Always use App Passwords, never your main Gmail password
- Keep the vendor directory in .gitignore
- Regularly update dependencies
- Monitor failed login attempts
- Review password reset logs periodically

## Development Notes

### For Team Members

1. Create your own `config/mail.php` from the example
2. Generate your own Gmail App Password
3. Use separate test email accounts for development
4. Never push sensitive configuration files

### Setting Up Test Environment

1. Create test staff accounts
2. Configure test email accounts
3. Verify password reset flow
4. Check email templates
5. Test token expiration
