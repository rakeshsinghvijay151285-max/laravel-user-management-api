# Installation & Setup Guide

## Prerequisites

- PHP 8.1+
- Composer
- MySQL 8.0+
- Redis 5.0+ (optional, for caching)
- Node.js 16+ (optional, for frontend assets)

## Step-by-Step Installation

### 1. Database Setup

#### Option A: Import SQL File
```bash
mysql -u root -p < database/laravel_user_management.sql
```

#### Option B: Run Migrations
```bash
# Create database manually in MySQL first
CREATE DATABASE laravel_user_management;

# Then run migrations
php artisan migrate
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Configure .env
```env
APP_NAME="Laravel User Management API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_user_management
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=database

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### 3. Install PHP Dependencies

```bash
composer install
```

### 4. Install Passport

```bash
php artisan passport:install --unauthenticated
```

### 5. Setup Storage

```bash
# Create storage link
php artisan storage:link

# Set permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 6. Setup Telescope (Debugging)

```bash
php artisan telescope:install
php artisan migrate
```

### 7. Create JWT Secrets (if needed)

```bash
php artisan passport:keys
```

### 8. Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Running the Application

### Development Mode

```bash
# Terminal 1: Start development server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Start queue worker
php artisan queue:work --tries=3

# Terminal 3: Start scheduler (optional)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Docker Setup

```bash
# Copy environment file
cp .env.example .env
echo "APP_KEY=" >> .env

# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Install Passport
docker-compose exec app php artisan passport:install

# Access application
# http://localhost
# http://localhost/api
```

## Testing

### Setup Test Database

```bash
# Create test database
CREATE DATABASE laravel_user_management_test;
```

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/UserControllerTest.php

# Run with coverage
php artisan test --coverage

# Run only failed tests
php artisan test --only-failed
```

## Initial Data

### Create Admin User via Tinker

```bash
php artisan tinker

# Create user
$user = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'phone' => '+1234567890',
    'password' => bcrypt('password123'),
    'status' => 'active'
]);

# Generate personal access token
$token = $user->createToken('Personal Token')->accessToken;
echo $token;
```

### Seed Test Data

```bash
# Create factory
php artisan make:factory UserFactory --model=User

# Create seeder
php artisan make:seeder UserSeeder

# Run seeder
php artisan db:seed --class=UserSeeder
```

## Configuration Details

### Cache Configuration

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
    ],
]
```

### Queue Configuration

```php
// config/queue.php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'retry_after' => 90,
    ],
]
```

### Mail Configuration

```php
// config/mail.php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS'),
    'name' => env('MAIL_FROM_NAME'),
],
```

## Common Issues & Solutions

### Issue: Migration Fails
```bash
# Check migration status
php artisan migrate:status

# Rollback and retry
php artisan migrate:reset
php artisan migrate
```

### Issue: Queue Jobs Not Processing
```bash
# Check failed jobs
php artisan queue:failed

# Restart queue worker
php artisan queue:work --tries=3 --timeout=90
```

### Issue: Cache Not Working
```bash
# Clear cache
php artisan cache:clear

# Flush Redis
redis-cli FLUSHALL

# Check Redis connection
redis-cli ping
```

### Issue: Messages Not Sending
```bash
# Check log file
tail -f storage/logs/laravel.log

# Test mail configuration
php artisan tinker
> Mail::raw('Test', function($message) { $message->to('test@example.com'); });
```

### Issue: Passport Tokens Not Working
```bash
# Check OAuth clients
php artisan passport:client --personal

# Generate new keys
php artisan passport:keys
```

## Performance Optimization

### Enable Caching

```bash
# Configuration caching
php artisan config:cache

# Route caching
php artisan route:cache

# View caching
php artisan view:cache
```

### Database Optimization

```bash
# Optimize database
php artisan db:seed --only-production

# Create query indexes
php artisan migrate:fresh --seed
```

### Queue Performance

```bash
# Process multiple jobs
php artisan queue:work --tries=3 --timeout=90 --memory=256 --processes=4
```

## Monitoring

### Check Application Health

```bash
# Via Telescope
http://localhost:8000/telescope

# Check logs
tail -f storage/logs/laravel.log
```

### Database Monitoring

```bash
# Check database size
SELECT table_schema, SUM(data_length + index_length) as size 
FROM information_schema.tables 
GROUP BY table_schema;

# Check query performance
EXPLAIN SELECT * FROM users WHERE status = 'active';
```

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate strong `APP_KEY`
- [ ] Configure `APP_URL`
- [ ] Setup SSL/HTTPS
- [ ] Configure database backups
- [ ] Setup error monitoring (Sentry, etc.)
- [ ] Configure log rotation
- [ ] Setup automated queue workers
- [ ] Configure cron jobs for scheduler
- [ ] Enable HTTP caching headers
- [ ] Setup CDN for static assets

### Environment Configuration

```env
# Production .env settings
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://api.yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=cookie

# Database (use strong credentials)
DB_CONNECTION=mysql
DB_HOST=db.yourdomain.com
DB_PORT=3306
DB_DATABASE=laravel_user_management
DB_USERNAME=api_user
DB_PASSWORD=strong_password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_key
```

## Backup & Recovery

### Database Backup

```bash
# Backup
mysqldump -u root -p laravel_user_management > backup.sql

# Restore
mysql -u root -p laravel_user_management < backup.sql
```

### Application Backup

```bash
# Tar application
tar -czf app-backup.tar.gz .

# Exclude unnecessary files
tar --exclude='node_modules' --exclude='vendor' --exclude='.env' -czf app-backup.tar.gz .
```

## Support & Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Passport Documentation](https://laravel.com/docs/9.x/passport)
- [Scout Documentation](https://laravel.com/docs/9.x/scout)
- [Telescope Documentation](https://laravel.com/docs/9.x/telescope)

---

**Documentation Version:** 1.0  
**Last Updated:** 2024-01-15
