#!/bin/bash
# VisionHub Deployment Script for cPanel

echo "Starting VisionHub deployment..."

# Navigate to project directory
cd /home/yourusername/public_html

# Pull latest code (if using Git)
if [ -d ".git" ]; then
    echo "Pulling latest code from repository..."
    git pull origin main
else
    echo "No Git repository found. Skipping code pull."
fi

# Install/Update PHP dependencies
echo "Installing/Updating PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Copy production environment file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Copying production environment file..."
    cp .env.production .env
fi

# Generate application key if not exists
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Clear and cache configurations
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Set proper permissions
echo "Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Deployment completed successfully!"