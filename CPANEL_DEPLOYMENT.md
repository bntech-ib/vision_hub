# cPanel Deployment Guide for VisionHub

## Prerequisites
1. cPanel account with:
   - PHP 8.2 or higher
   - MySQL 8.0 or higher
   - SSH access (recommended)
   - Composer installed
2. Domain name configured in cPanel

## Deployment Steps

### 1. File Upload
Upload the application files to your cPanel account:
- Upload all files except the `public` directory to `/home/yourusername/visionhub`
- Upload the contents of the `public` directory to `/home/yourusername/public_html`

### 2. Database Setup
1. Create a new MySQL database in cPanel
2. Create a new database user and assign it to the database
3. Import the database schema (if provided) or run migrations

### 3. Environment Configuration
1. Copy `.env.production` to `.env` in the root directory
2. Update the database credentials in `.env`:
   ```
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

### 4. Application Setup
1. Generate the application key:
   ```bash
   php artisan key:generate
   ```

2. Run database migrations:
   ```bash
   php artisan migrate --force
   ```

3. Optimize the application:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### 5. File Permissions
Set proper permissions for storage directories:
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## cPanel-Specific Configurations

### Cron Jobs
Add the following cron jobs via cPanel:
```
# Laravel Scheduler (runs every minute)
* * * * * cd /home/yourusername/public_html && php artisan schedule:run >> /dev/null 2>&1

# Queue Worker
* * * * * cd /home/yourusername/public_html && php artisan queue:work --once --quiet
```

### Subdomain Setup (Optional)
If you want to use a subdomain for the API:
1. Create a subdomain in cPanel (e.g., api.yourdomain.com)
2. Point it to the same public directory
3. Update the APP_URL in .env accordingly

## Troubleshooting

### Common Issues
1. **500 Internal Server Error**
   - Check file permissions
   - Verify .env file exists and is properly configured
   - Check error logs in cPanel

2. **404 Not Found for API Routes**
   - Ensure .htaccess file is in place
   - Verify mod_rewrite is enabled

3. **Database Connection Error**
   - Double-check database credentials
   - Ensure the database user has proper privileges

### Useful Commands
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check application status
php artisan tinker
>>> App\Models\User::count()

# Run specific migration
php artisan migrate --path=/database/migrations/2023_01_01_create_users_table.php
```

## Security Recommendations

1. **File Permissions**
   - Set proper permissions (755 for directories, 644 for files)
   - Ensure sensitive files are not accessible via web

2. **SSL Certificate**
   - Install an SSL certificate for HTTPS
   - Update APP_URL in .env to use https://

3. **Environment File**
   - Never commit .env file to version control
   - Use strong, random passwords

4. **Regular Updates**
   - Keep Laravel and dependencies updated
   - Monitor security advisories