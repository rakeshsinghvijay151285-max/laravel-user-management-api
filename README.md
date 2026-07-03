# Laravel 10 User Management API

A comprehensive, production-ready API for user management with advanced Laravel concepts including CRUD operations, file uploads, authentication, caching, queuing, and event handling.

## Features

### Core Features
- ✅ **User CRUD Operations** - Create, Read, Update, Delete users
- ✅ **File Upload Management** - Upload, download, and manage user files
- ✅ **User Profiles** - Extended user information with addresses and social media links
- ✅ **Authentication** - Laravel Passport for OAuth2 token-based authentication

### Advanced Laravel Concepts
- ✅ **Eloquent ORM** - Advanced model relationships and queries
- ✅ **Query Builder** - Complex database queries
- ✅ **Event System** - Event binding and listeners for user actions
- ✅ **Job Queue** - Asynchronous job processing with database driver
- ✅ **Cache Management** - Redis cache for performance optimization
- ✅ **Email Notifications** - Welcome emails and file upload notifications
- ✅ **Validation** - Form request validation with custom rules
- ✅ **MVC Structure** - Clean separation of concerns with Models, Views, Controllers
- ✅ **Service Layer** - Business logic encapsulation in services
- ✅ **Repository Pattern** - Data access abstraction layer
- ✅ **Resource Classes** - API response transformation
- ✅ **Middleware** - Authentication and request filtering
- ✅ **Scheduled Tasks** - Artisan command scheduling
- ✅ **Kafka Integration** - Event streaming to Kafka
- ✅ **Laravel Scout** - Full-text search capability
- ✅ **Laravel Telescope** - Application monitoring and debugging
- ✅ **Security** - Password hashing, CSRF protection, validation

## Project Structure

```
laravel-user-management-api/
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   └── ClearExpiredTokens.php
│   │   └── Kernel.php
│   ├── Events/
│   │   ├── FileUploaded.php
│   │   ├── UserCreated.php
│   │   ├── UserDeleted.php
│   │   └── UserUpdated.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── FileUploadController.php
│   │   │   │   └── UserController.php
│   │   │   └── Controller.php
│   │   ├── Requests/
│   │   │   ├── FileUploadRequest.php
│   │   │   ├── StoreUserRequest.php
│   │   │   └── UpdateUserRequest.php
│   │   └── Resources/
│   │       ├── UserFileResource.php
│   │       ├── UserProfileResource.php
│   │       └── UserResource.php
│   ├── Jobs/
│   │   ├── ProcessUserData.php
│   │   ├── PublishUserEventToKafka.php
│   │   └── SendEmailVerification.php
│   ├── Listeners/
│   │   ├── LogFileUpload.php
│   │   ├── LogUserUpdate.php
│   │   └── SendWelcomeEmail.php
│   ├── Mail/
│   │   ├── FileUploadNotification.php
│   │   └── WelcomeMail.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── UserFile.php
│   │   └── UserProfile.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── EventServiceProvider.php
│   ├── Repositories/
│   │   └── UserRepository.php
│   └── Services/
│       ├── FileUploadService.php
│       └── UserService.php
├── config/
│   ├── cache.php
│   ├── mail.php
│   ├── queue.php
│   ├── scout.php
│   ├── session.php
│   └── telescope.php
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_02_000000_create_user_profiles_table.php
│   │   ├── 0001_01_03_000000_create_user_files_table.php
│   │   ├── 0001_01_04_000000_create_oauth_clients_table.php
│   │   ├── 0001_01_05_000000_create_oauth_access_tokens_table.php
│   │   ├── 0001_01_06_000000_create_oauth_refresh_tokens_table.php
│   │   ├── 0001_01_07_000000_create_jobs_table.php
│   │   ├── 0001_01_08_000000_create_cache_table.php
│   │   └── 0001_01_09_000000_create_failed_jobs_table.php
│   └── laravel_user_management.sql
├── resources/
│   └── mail/
│       ├── file-upload-notification.html
│       └── welcome.html
├── routes/
│   ├── api.php
│   └── web.php
├── storage/
│   └── uploads/
├── .env.example
├── composer.json
└── README.md
```

## Installation

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Redis (for cache and queue)
- Composer
- Apache/Nginx

### Setup Steps

1. **Clone or Navigate to Project**
```bash
cd laravel-user-management-api
```

2. **Install Dependencies**
```bash
composer install
```

3. **Copy Environment File**
```bash
cp .env.example .env
```

4. **Generate Application Key**
```bash
php artisan key:generate
```

5. **Create Database**
```bash
# Import the SQL file
mysql -u root -p < database/laravel_user_management.sql

# Or create manually and run migrations
php artisan migrate
```

6. **Install Passport**
```bash
php artisan passport:install
```

7. **Create Storage Link**
```bash
php artisan storage:link
```

8. **Publish Telescope**
```bash
php artisan telescope:install
php artisan migrate
```

9. **Create Scout Indices** (if using Algolia)
```bash
php artisan tinker
> \App\Models\User::makeAllSearchable()
```

10. **Start Queue Worker**
```bash
php artisan queue:work
```

11. **Start Scheduler** (in production)
```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

12. **Start Development Server**
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user

### Users
- `GET /api/users` - List all users (paginated)
- `GET /api/users/{id}` - Get user by ID
- `POST /api/users` - Create new user (requires auth)
- `PUT /api/users/{id}` - Update user (requires auth)
- `DELETE /api/users/{id}` - Delete user (requires auth)
- `GET /api/users/search?q=query` - Search users (requires auth)

### Files
- `POST /api/files/upload` - Upload file (requires auth)
- `GET /api/files` - List user files (requires auth)
- `GET /api/files/{fileId}/download` - Download file (requires auth)
- `DELETE /api/files/{fileId}` - Delete file (requires auth)

## API Usage Examples

### Register User
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Create Personal Access Token
```bash
php artisan tinker
> $user = App\Models\User::find(1);
> $token = $user->createToken('Personal Token')->accessToken;
```

### Get All Users
```bash
curl -H "Authorization: Bearer {token}" \
  http://localhost:8000/api/users
```

### Update User
```bash
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe",
    "bio": "Updated bio"
  }'
```

### Upload File
```bash
curl -X POST http://localhost:8000/api/files/upload \
  -H "Authorization: Bearer {token}" \
  -F "file=@/path/to/file.pdf"
```

## Core Concepts Implemented

### 1. Eloquent ORM
- Model relationships (hasOne, hasMany, belongsTo)
- Query scoping and eager loading
- Mass assignment protection
- Model events and observers

### 2. Events & Listeners
- Event binding in EventServiceProvider
- Async event listeners with ShouldQueue
- Event dispatching from services
- Multiple listeners per event

### 3. Job Queue
- Database queue driver
- Job serialization and handling
- Failed job tracking
- Background processing of emails and data

### 4. Caching
- Redis cache driver configuration
- Cache remember patterns in repositories
- Cache invalidation on data changes
- Cache key management

### 5. Authentication (Passport)
- OAuth2 token generation
- Personal access tokens
- Scope-based authorization
- Token refresh mechanism

### 6. Email Notifications
- Mailable classes with templates
- Queued email delivery
- HTML email formatting

### 7. Validation
- Form request classes
- Custom validation rules
- Unique constraint validation
- File upload validation

### 8. Scheduled Tasks
- Kernel scheduling configuration
- Automated cache cleanup
- Token expiration handling
- Job scheduling

### 9. Kafka Integration
- Message publishing to Kafka brokers
- Event streaming for user actions
- Async queue job for Kafka publishing

### 10. Security
- Password hashing with bcrypt
- CSRF protection (in web routes)
- Authorization middleware
- Input validation and sanitization
- SQL injection prevention via ORM

### 11. Scout Search
- Full-text search indexing
- Searchable trait integration
- Algolia integration ready

### 12. Telescope Monitoring
- Request tracking
- Exception logging
- Query performance analysis
- Job monitoring

## Database Schema

The application includes comprehensive database schema with:
- Users table with status tracking
- User profiles for extended information
- User files for file management
- OAuth tables for Passport authentication
- Job queue tables for async processing
- Cache and session tables

See [database/laravel_user_management.sql](database/laravel_user_management.sql) for complete schema.

## Configuration

### Environment Variables
- `CACHE_DRIVER` - Set to `redis` for production
- `QUEUE_CONNECTION` - Set to `database` or `redis`
- `MAIL_MAILER` - Configure SMTP or other mailers
- `KAFKA_BROKER` - Kafka broker address
- `SCOUT_DRIVER` - Set to `algolia` for search
- `TELESCOPE_ENABLED` - Enable/disable monitoring

### Redis Configuration
```
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue Configuration
```
QUEUE_CONNECTION=database
```

## Testing

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## Performance Optimization

1. **Database Indexing** - Strategic indexes on frequently queried columns
2. **Caching** - Redis for high-hit data
3. **Queue Jobs** - Offload heavy processing
4. **Pagination** - Limit returned records
5. **Lazy Loading Protection** - Eager load relationships

## Security Best Practices

1. ✅ Environment variable protection
2. ✅ Password hashing with bcrypt
3. ✅ Token-based authentication
4. ✅ Input validation and sanitization
5. ✅ SQL injection prevention via ORM
6. ✅ CORS configuration ready
7. ✅ Rate limiting ready for implementation

## Monitoring & Debugging

- **Telescope Dashboard** - Access at `/telescope`
- **Logs** - Check `storage/logs/laravel.log`
- **Database Queries** - Monitor via Telescope
- **Queue Jobs** - Track in database
- **Failed Jobs** - Review in `failed_jobs` table

## Version Information

- **Laravel**: 10.x
- **PHP**: 8.1+
- **MySQL**: 8.0+
- **Redis**: 5.0+
- **Kafka**: 2.x (optional)

## Troubleshooting

### Queue Jobs Not Processing
```bash
php artisan queue:work --tries=3
```

### Cache Not Working
- Ensure Redis is running
- Check `REDIS_HOST` and `REDIS_PORT` in .env

### Email Not Sending
- Configure MAIL_* in .env
- Check mail.log in storage/logs
- Use `php artisan tinker` to test

### Passport Tokens Not Working
- Run `php artisan passport:install`
- Verify token in Authorization header

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Commit and push
5. Create a Pull Request

## License

This project is open-source and available under the MIT license.

## Support

For issues and questions:
- Check documentation
- Review example API calls
- Check logs for errors
- Use Telescope for debugging

## Authors

Created with comprehensive Laravel best practices and advanced concepts.

---

**Last Updated**: 2024
**Status**: Production Ready
