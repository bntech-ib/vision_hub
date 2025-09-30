# VisionHub Backend - Laravel API & Admin Dashboard

## 📋 Overview

VisionHub Backend is a comprehensive Laravel-based server application that provides RESTful APIs and an admin dashboard to support the VisionHub frontend application. It handles user authentication, ad management, marketplace functionality, sponsored content, online courses, brain teasers, and financial transactions.

## 🏗️ Architecture

- **Framework**: Laravel 10.x
- **Database**: MySQL 8.0+
- **Frontend (Admin)**: Blade Templates with Bootstrap 5
- **API**: RESTful JSON APIs
- **Authentication**: Laravel Sanctum (SPA & API tokens)
- **File Storage**: Laravel Storage (local/cloud)
- **Queue System**: Redis/Database queues
- **Cache**: Redis/File cache

## 🛠️ Tech Stack

### Core Technologies
- **PHP**: 8.1+
- **Laravel**: 10.x
- **MySQL**: 8.0+
- **Redis**: 6.0+ (for caching & queues)
- **Composer**: 2.0+

### Frontend (Admin Dashboard)
- **Bootstrap**: 5.3.x
- **Blade Templates**: Laravel's templating engine
- **jQuery**: 3.6.x
- **Chart.js**: 4.x (for analytics)
- **DataTables**: 1.13.x (for data management)

### Additional Libraries
- **Laravel Sanctum**: API authentication
- **Laravel Cashier**: Payment processing (optional)
- **Spatie Laravel Permission**: Role & permission management
- **Laravel Telescope**: Debugging & monitoring
- **Intervention Image**: Image processing

## 📁 Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── API/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── AdController.php
│   │   │   │   ├── MarketplaceController.php
│   │   │   │   ├── SponsoredPostController.php
│   │   │   │   ├── CourseController.php
│   │   │   │   ├── BrainTeaserController.php
│   │   │   │   ├── TransactionController.php
│   │   │   │   └── WithdrawalController.php
│   │   │   └── Admin/
│   │   │       ├── DashboardController.php
│   │   │       ├── UserController.php
│   │   │       ├── AdController.php
│   │   │       ├── CourseController.php
│   │   │       └── ReportController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   └── ApiThrottleMiddleware.php
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   │   ├── User.php
│   │   ├── UserPackage.php
│   │   ├── Advertisement.php
│   │   ├── AdInteraction.php
│   │   ├── Product.php
│   │   ├── SponsoredPost.php
│   │   ├── Course.php
│   │   ├── CourseEnrollment.php
│   │   ├── BrainTeaser.php
│   │   ├── BrainTeaserAttempt.php
│   │   ├── Transaction.php
│   │   └── WithdrawalRequest.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── PaymentService.php
│   │   ├── NotificationService.php
│   │   └── AnalyticsService.php
│   └── Jobs/
│       ├── ProcessAdView.php
│       ├── SendNotification.php
│       └── ProcessWithdrawal.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── admin/
│   │   │   ├── layouts/
│   │   │   ├── dashboard/
│   │   │   ├── users/
│   │   │   ├── ads/
│   │   │   ├── courses/
│   │   │   └── reports/
│   │   └── emails/
│   └── js/
│       └── admin/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── admin.php
├── storage/
│   ├── app/
│   │   ├── uploads/
│   │   │   ├── avatars/
│   │   │   ├── course-thumbnails/
│   │   │   ├── product-images/
│   │   │   └── ad-images/
│   └── logs/
└── tests/
    ├── Feature/
    └── Unit/
```

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- Composer 2.0+
- MySQL 8.0+
- Redis 6.0+ (optional but recommended)
- Node.js 16+ & npm (for admin asset compilation)

### Installation Steps

1. **Clone the repository**
```bash
git clone <repository-url>
cd visionhub-backend
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=visionhub
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Configure Redis (optional)**
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

6. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

7. **Install Laravel Sanctum**
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

8. **Install frontend dependencies (for admin dashboard)**
```bash
npm install
npm run build
```

9. **Create storage symlink**
```bash
php artisan storage:link
```

10. **Start the development server**
```bash
php artisan serve
```

11. **Start queue workers (optional)**
```bash
php artisan queue:work
```

### cPanel Deployment

For cPanel hosting, please refer to the [cPanel Deployment Guide](CPANEL_DEPLOYMENT.md) for detailed instructions.

#### Quick cPanel Setup Steps:
1. Upload files to your cPanel account
2. Create MySQL database and user
3. Configure `.env` file with database credentials
4. Run `php artisan key:generate`
5. Run `php artisan migrate --force`
6. Set proper file permissions
7. Configure cron jobs for scheduled tasks

## 🔧 Configuration

### Environment Variables

```env
# Application
APP_NAME=VisionHub
APP_ENV=local
APP_KEY=base64:generated_key
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=visionhub
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls

# Payment Gateway (Optional)
PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxx
PAYSTACK_SECRET_KEY=sk_test_xxxxxxxx

# File Storage
FILESYSTEM_DISK=local
```

## 📊 Database Schema

### Core Tables

#### Users Table
```sql
- id (bigint, primary key)
- username (varchar, unique)
- email (varchar, unique)
- full_name (varchar)
- phone (varchar, nullable)
- country (varchar, nullable)
- package_id (bigint, foreign key)
- profile_image (varchar, nullable)
- referral_code (varchar, unique)
- email_verified_at (timestamp, nullable)
- password (varchar)
- remember_token (varchar, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

#### User Packages Table
```sql
- id (bigint, primary key)
- name (varchar)
- price (decimal)
- benefits (json)
- duration_days (integer)
- is_active (boolean, default true)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Advertisements Table
```sql
- id (bigint, primary key)
- advertiser_id (bigint, foreign key to users)
- title (varchar)
- description (text)
- image_url (varchar)
- target_url (varchar)
- category (enum)
- budget (decimal)
- spent (decimal, default 0)
- impressions (bigint, default 0)
- clicks (bigint, default 0)
- status (enum: active, paused, completed, rejected)
- start_date (datetime)
- end_date (datetime)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Products Table
```sql
- id (bigint, primary key)
- seller_id (bigint, foreign key to users)
- title (varchar)
- description (text)
- price (decimal)
- currency (varchar, default 'NGN')
- category (enum)
- images (json)
- status (enum)
- stock (integer, default 1)
- location (varchar)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Courses Table
```sql
- id (bigint, primary key)
- title (varchar)
- description (text)
- instructor (varchar)
- instructor_image (varchar, nullable)
- thumbnail_url (varchar)
- category (enum)
- level (enum)
- duration_minutes (integer)
- lessons_count (integer)
- price (decimal)
- original_price (decimal, nullable)
- rating (decimal, default 0)
- students_count (integer, default 0)
- is_premium (boolean, default false)
- tags (json)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Brain Teasers Table
```sql
- id (bigint, primary key)
- title (varchar)
- question (text)
- options (json)
- correct_answer (integer)
- explanation (text)
- difficulty (enum)
- category (enum)
- points (integer)
- time_limit_seconds (integer)
- image_url (varchar, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

## 🔌 API Documentation

### Base URL
```
Development: http://localhost:8000/api/v1
Production: https://api.visionhub.com/api/v1
```

### Authentication
All API requests require authentication via Sanctum tokens:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Core API Endpoints

#### Authentication
```http
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/logout
POST /api/v1/auth/refresh
GET  /api/v1/auth/user
PUT  /api/v1/auth/profile
```

#### Dashboard
``http
GET /api/v1/dashboard/stats
GET /api/v1/dashboard/earnings
GET /api/v1/dashboard/notifications
```

#### Advertisements
```http
GET    /api/v1/ads
POST   /api/v1/ads
GET    /api/v1/ads/{id}
PUT    /api/v1/ads/{id}
DELETE /api/v1/ads/{id}
POST   /api/v1/ads/{id}/interact (view/click)
```

#### Marketplace
```http
GET    /api/v1/products
POST   /api/v1/products
GET    /api/v1/products/{id}
PUT    /api/v1/products/{id}
DELETE /api/v1/products/{id}
GET    /api/v1/products/categories
```

#### Courses
```http
GET  /api/v1/courses
GET  /api/v1/courses/{id}
POST /api/v1/courses/{id}/enroll
GET  /api/v1/courses/my-enrollments
PUT  /api/v1/courses/{id}/progress
```

#### Brain Teasers
```http
GET  /api/v1/brain-teasers
GET  /api/v1/brain-teasers/random
POST /api/v1/brain-teasers/{id}/attempt
GET  /api/v1/brain-teasers/stats
```

#### Transactions
```http
GET  /api/v1/transactions
POST /api/v1/withdrawals
GET  /api/v1/withdrawals
PUT  /api/v1/withdrawals/{id}/cancel
```

### Admin Dashboard API Endpoints

For detailed documentation of Admin Dashboard API endpoints, please refer to the [Admin API Documentation](ADMIN-API-DOCUMENTATION.md).

### API Response Format

#### Success Response
```json
{
  "success": true,
  "data": {},
  "message": "Operation successful",
  "meta": {
    "pagination": {},
    "timestamp": "2024-01-20T10:00:00Z"
  }
}
```

#### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  },
  "meta": {
    "timestamp": "2024-01-20T10:00:00Z"
  }
}
```

## 🔐 Admin Dashboard

### Admin Access

- URL: `http://localhost:8000/admin`
- Default credentials:
  - Email: `admin@visionhub.com`
  - Password: `admin123`

### Features

#### Dashboard Overview
- User statistics and growth charts
- Revenue analytics
- Recent transactions
- System health monitoring

#### User Management
- View all users with pagination and search
- User details and profile management
- Package assignment and modifications
- Account status management (active/suspended)

#### Advertisement Management
- Review and approve/reject ads
- Monitor ad performance
- Manage ad categories and pricing
- Revenue tracking per advertisement

#### Course Management
- Add, edit, and delete courses
- Course enrollment tracking
- Performance analytics
- Instructor management

#### Brain Teaser Management
- Create and manage brain teasers
- Difficulty and category management
- Performance analytics
- Leaderboard management

#### Financial Management
- Transaction monitoring
- Withdrawal request processing
- Revenue reports and analytics
- Payment gateway integration

#### Reports & Analytics
- User engagement reports
- Revenue and financial reports
- Course performance analytics
- System usage statistics

### Admin Dashboard Routes
```php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index']);
    Route::resource('users', AdminUserController::class);
    Route::resource('ads', AdminAdController::class);
    Route::resource('courses', AdminCourseController::class);
    Route::resource('brain-teasers', AdminBrainTeaserController::class);
    Route::get('reports', [AdminReportController::class, 'index']);
    Route::get('transactions', [AdminTransactionController::class, 'index']);
    Route::put('withdrawals/{id}/approve', [AdminWithdrawalController::class, 'approve']);
    Route::put('withdrawals/{id}/reject', [AdminWithdrawalController::class, 'reject']);
});
```

## 🔒 Security Features

### Authentication & Authorization
- Laravel Sanctum for API authentication
- Role-based access control (Admin, User)
- Password hashing with bcrypt
- Email verification for new accounts
- Rate limiting on API endpoints

### Data Protection
- CSRF protection for web routes
- Input validation and sanitization
- SQL injection prevention via Eloquent ORM
- XSS protection in Blade templates
- File upload validation and restrictions

### API Security
- API rate limiting (60 requests per minute)
- Request size limitations
- CORS configuration
- API token expiration management

## 🔄 Background Jobs & Queues

### Queue Configuration
```bash
# Start queue workers
php artisan queue:work

# Process specific queues
php artisan queue:work --queue=high,default,low

# Run queue worker as daemon
php artisan queue:work --daemon
```

### Background Jobs

#### ProcessAdView Job
- Records ad impressions and interactions
- Updates advertiser billing
- Calculates user earnings

#### SendNotification Job
- Sends email notifications
- Push notifications (if implemented)
- In-app notifications

#### ProcessWithdrawal Job
- Validates withdrawal requests
- Integrates with payment gateways
- Updates transaction records

## 📈 Performance Optimization

### Caching Strategy
- Database query caching
- API response caching
- Session storage in Redis
- File-based cache for static data

### Database Optimization
- Proper indexing on frequently queried columns
- Database query optimization
- Connection pooling
- Read/write splitting (for production)

### Code Optimization
- Lazy loading of relationships
- API resource transformers
- Efficient pagination
- Background job processing

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test files
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage
```

### Test Categories
- **Unit Tests**: Model logic, services, utilities
- **Feature Tests**: API endpoints, authentication, workflows
- **Integration Tests**: Database interactions, external services

## 🚀 Deployment

### Production Environment

#### Server Requirements
- PHP 8.1+ with required extensions
- MySQL 8.0+ or MariaDB 10.3+
- Redis 6.0+
- Nginx or Apache web server
- SSL certificate

#### Deployment Steps
```bash
# 1. Clone and setup
git clone <repository-url>
composer install --no-dev --optimize-autoloader

# 2. Environment configuration
cp .env.production .env
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Database setup
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder

# 4. Storage setup
php artisan storage:link

# 5. Queue worker setup (supervisor)
php artisan queue:restart
```

#### Nginx Configuration
```
server {
    listen 80;
    server_name api.visionhub.com;
    root /var/www/visionhub-backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 📚 Additional Resources

### Useful Commands
```bash
# Generate new controller
php artisan make:controller API/ExampleController --api

# Generate new model with migration
php artisan make:model Example -m

# Generate new request validation
php artisan make:request ExampleRequest

# Generate new job
php artisan make:job ProcessExample

# Generate new seeder
php artisan make:seeder ExampleSeeder

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Documentation Links
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Bootstrap 5](https://getbootstrap.com/docs/5.3/)
- [Chart.js](https://www.chartjs.org/docs/)

## 🐛 Troubleshooting

### Common Issues

#### Storage Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/visionhub-backend/storage
sudo chmod -R 775 /var/www/visionhub-backend/storage
```

#### Queue Jobs Not Processing
```bash
# Check queue status
php artisan queue:work --timeout=60

# Clear failed jobs
php artisan queue:flush

# Restart queue workers
php artisan queue:restart
```

#### Database Connection Issues
- Verify database credentials in `.env`
- Check MySQL service status
- Ensure database exists and user has proper privileges

## 📞 Support

For technical support or questions:
- **Email**: dev@visionhub.com
- **Documentation**: [docs.visionhub.com](https://docs.visionhub.com)
- **Issue Tracking**: GitHub Issues

## 📝 License

This project is proprietary software. All rights reserved.

---

**Version**: 1.0.0  
**Last Updated**: January 2024  
**Maintained by**: VisionHub Development Team