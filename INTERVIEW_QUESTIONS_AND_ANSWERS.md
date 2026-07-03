# Laravel User Management API - Interview Questions & Answers
**Comprehensive Guide: Basic to Advanced Level + Security**

---

## Table of Contents
1. [Basic Level Questions](#basic-level-questions)
2. [Intermediate Level Questions](#intermediate-level-questions)
3. [Advanced Level Questions](#advanced-level-questions)
4. [Security-Related Questions](#security-related-questions)

---

## BASIC LEVEL QUESTIONS

### Q1: What is the purpose of this Laravel project?
**Answer:**
This is a production-ready Laravel 10 API for user management that demonstrates core Laravel concepts and best practices. It provides:
- User CRUD operations (Create, Read, Update, Delete)
- User authentication and authorization using OAuth2 Passport
- File upload and management functionality
- Event-driven architecture with listeners
- Queue-based job processing
- Caching with Redis
- Search functionality with Laravel Scout
- Email notifications
- Complete API documentation and Docker setup

### Q2: What is the project structure and why is it organized this way?
**Answer:**
The project follows Laravel's MVC with additional layers:
```
app/
├── Console/        - CLI commands
├── Events/         - Event classes (UserCreated, UserUpdated)
├── Http/           - Controllers, Requests, Resources
├── Jobs/           - Queue jobs
├── Listeners/      - Event listeners
├── Mail/           - Mailable classes
├── Models/         - Eloquent models
├── Providers/      - Service providers
├── Repositories/   - Data access layer
└── Services/       - Business logic layer
```

This structure provides:
- **Separation of Concerns**: Each layer has a specific responsibility
- **Maintainability**: Code is organized logically
- **Testability**: Each component can be tested independently
- **Scalability**: Easy to add new features without affecting existing code

### Q3: What is Eloquent ORM and how is it used in this project?
**Answer:**
Eloquent ORM is Laravel's object-relational mapper that provides an elegant syntax for interacting with databases. In this project:

**Models:**
```php
class User extends Authenticatable {
    use HasApiTokens;
    protected $fillable = ['name', 'email', 'phone', 'avatar', 'bio', 'password', 'email_verified_at', 'status'];
    public function profile() { return $this->hasOne(UserProfile::class); }
    public function files() { return $this->hasMany(UserFile::class); }
}
```

**Relationships:**
- `User hasOne UserProfile` - One-to-one relationship
- `User hasMany UserFile` - One-to-many relationship

**Usage:**
- Querying: `User::find($id)` or `User::where('email', 'john@example.com')->first()`
- Creating: `User::create([...])`
- Updating: `$user->update([...])`
- Deleting: `$user->delete()`

### Q4: Explain the Model-View-Controller (MVC) pattern used in this project.
**Answer:**
MVC is an architectural pattern that separates concerns into three components:

1. **Model (User, UserProfile, UserFile)**
   - Represents data and business rules
   - Handles database interactions through Eloquent
   - Defines relationships between entities

2. **View (HTTP Resources)**
   - Transforms model data for API responses
   - `UserResource`, `UserProfileResource`, `UserFileResource`
   - Formats JSON responses consistently

3. **Controller (UserController, FileUploadController)**
   - Handles HTTP requests and responses
   - Orchestrates business logic via services
   - Performs input validation
   - Returns appropriate HTTP responses

**Flow:**
Request → Route → Controller → Service → Repository → Model → Database

### Q5: What is the purpose of Form Requests (StoreUserRequest, UpdateUserRequest)?
**Answer:**
Form Requests encapsulate validation logic and authorization separately from controllers. Benefits:

```php
class StoreUserRequest extends FormRequest {
    public function authorize(): bool { return true; }
    
    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
    
    public function messages(): array {
        return ['email.unique' => 'Email already exists'];
    }
}
```

**Advantages:**
- Centralized validation rules
- Automatic request validation before controller method
- Easy to reuse validation logic
- Cleaner controller code
- Custom error messages

### Q6: What are HTTP Resources (UserResource, UserFileResource) and why use them?
**Answer:**
HTTP Resources transform model data into JSON responses, providing a consistent API contract. Example:

```php
class UserResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'status' => $this->status,
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            'files_count' => $this->files()->count(),
            'created_at' => $this->created_at,
        ];
    }
}
```

**Benefits:**
- Consistent response formatting
- Hide sensitive fields (password not included)
- Transform data without modifying models
- Support nested relationships
- Conditional data inclusion with `whenLoaded()`

### Q7: Explain the Service Layer (UserService, FileUploadService) and its purpose.
**Answer:**
The Service Layer contains business logic and orchestrates interactions between controllers, repositories, and other services. Example:

```php
class UserService {
    protected $userRepository;
    
    public function createUser(array $data) {
        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepository->createUser($data);
        
        // Business logic
        event(new UserCreated($user));
        ProcessUserData::dispatch($user);
        PublishUserEventToKafka::dispatch($user->id, 'user_created', $data);
        
        return $user;
    }
}
```

**Purpose:**
- Encapsulates complex business logic
- Makes controllers thin and focused on HTTP handling
- Enables code reuse across different endpoints
- Facilitates testing by isolating business logic
- Handles event dispatching and job queuing

### Q8: What is the Repository Pattern and how is it used here?
**Answer:**
The Repository Pattern abstracts database access, serving as an intermediary between the Service Layer and Models. Example:

```php
class UserRepository {
    public function getAllUsers($page = 1, $perPage = 15) {
        $cacheKey = "users_page_{$page}_{$perPage}";
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($perPage) {
            return User::paginate($perPage);
        });
    }
    
    public function getUserById($id) {
        return Cache::remember("user_{$id}", now()->addHours(24), function () use ($id) {
            return User::with('profile', 'files')->find($id);
        });
    }
}
```

**Advantages:**
- Centralizes database queries
- Simplifies testing (can mock repository)
- Makes code more maintainable
- Implements caching logic in one place
- Easier to switch databases later

### Q9: What is middleware in Laravel and which ones are used in this project?
**Answer:**
Middleware provides a mechanism for filtering HTTP requests. In this project:

**Route Middleware:**
```php
Route::post('/auth/register', [UserController::class, 'store']); // No middleware

Route::middleware('auth:api')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```

**Key Middleware:**
- `auth:api` - Verifies user is authenticated via Passport tokens
- `verified` - Ensures email is verified (when needed)
- `throttle` - Rate limiting (could be added)
- `cors` - Cross-Origin Resource Sharing
- `json.response` - Ensures JSON responses

**Middleware Pipeline:**
HTTP Request → Middleware Stack → Controller → Response

### Q10: What is OAuth2 Passport and how does it work in this project?
**Answer:**
OAuth2 Passport provides API authentication using access tokens and personal access tokens.

**Setup:**
```bash
php artisan passport:install  # Creates OAuth2 client credentials
```

**Token-Based Authentication:**
```php
Route::middleware('auth:api')->group(function () {
    // Protected routes
    Route::get('/users', [UserController::class, 'index']);
});

// Client sends: Authorization: Bearer {access_token}
```

**User Model Changes:**
```php
class User extends Authenticatable {
    use HasApiTokens;  // Enables Passport tokens
    
    public function findForPassport($identifier) {
        return $this->orWhere('email', $identifier)
                    ->orWhere('phone', $identifier)
                    ->first();
    }
}
```

**Benefits:**
- Stateless authentication (no sessions needed)
- Secure token-based access
- Multiple token types (client credentials, password grants)
- Token expiration and refresh capabilities

---

## INTERMEDIATE LEVEL QUESTIONS

### Q11: How does pagination work in this project? Provide an example.
**Answer:**
Pagination allows fetching large datasets in manageable chunks. Implementation:

```php
// Repository - UserRepository.php
public function getAllUsers($page = 1, $perPage = 15) {
    $cacheKey = "users_page_{$page}_{$perPage}";
    return Cache::remember($cacheKey, now()->addHours(1), function () use ($perPage) {
        return User::paginate($perPage);
    });
}

// Controller - UserController.php
public function index() {
    $users = $this->userService->getAllUsers(
        request()->get('page', 1),
        request()->get('per_page', 15)
    );
    
    return response()->json([
        'data' => UserResource::collection($users),
        'pagination' => [
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ],
    ]);
}

// API Request: GET /api/users?page=1&per_page=20
// SQLQuery: SELECT * FROM users LIMIT 20 OFFSET 0;
```

### Q12: Explain the event-driven architecture in this project.
**Answer:**
Events allow decoupling of components. When something happens, an event is fired and listeners react asynchronously.

**Event Definition:**
```php
class UserCreated {
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $user;
    
    public function __construct(User $user) {
        $this->user = $user;
    }
}
```

**Event Registration (EventServiceProvider):**
```php
protected $listen = [
    UserCreated::class => [
        SendWelcomeEmail::class,  // Listener
    ],
];
```

**Event Dispatching (UserService):**
```php
public function createUser(array $data) {
    $data['password'] = Hash::make($data['password']);
    $user = $this->userRepository->createUser($data);
    
    event(new UserCreated($user));  // Fire event
    
    return $user;
}
```

**Listener (SendWelcomeEmail):**
```php
class SendWelcomeEmail implements ShouldQueue {
    public function handle(UserCreated $event): void {
        Mail::to($event->user->email)->send(new WelcomeMail($event->user));
    }
}
```

**Flow:**
1. User is created → Event is dispatched
2. EventServiceProvider routes event to listeners
3. ShouldQueue ensures execution in background queue
4. Email is sent asynchronously without blocking request

### Q13: How does caching work in this project? What are the cache keys?
**Answer:**
Caching reduces database queries by storing frequently accessed data in Redis.

**Cache Implementation:**
```php
// Repository method
public function getUserById($id) {
    // Try to retrieve from cache first
    return Cache::remember("user_{$id}", now()->addHours(24), function () use ($id) {
        // If not cached, query database
        return User::with('profile', 'files')->find($id);
    });
}

// Cache.remember() checks cache first:
// - If hit: return cached value
// - If miss: execute closure, store result, return it
```

**Cache Keys Pattern:**
| Key Pattern | Expiration | Purpose |
|-------------|-----------|---------|
| `user_{id}` | 24 hours | Individual user data with relationships |
| `users_page_{page}_{per_page}` | 1 hour | Paginated user list |

**Cache Invalidation (when data changes):**
```php
public function updateUser($id, array $data) {
    $user = $this->userRepository->updateUser($id, $data);
    
    // Clear related cache entries
    Cache::forget("user_{$id}");
    Cache::forget('users_page_*');  // Forget all pagination caches
    
    return $user;
}
```

**Why This Matters:**
- Reduces database load significantly
- Improves API response times
- Better user experience
- Scalability for high traffic

### Q14: Explain the queueing system and how jobs are processed.
**Answer:**
Queuing allows asynchronous processing of long-running tasks without blocking user requests.

**Queue Jobs:**
```php
class ProcessUserData implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $user;
    
    public function __construct(User $user) {
        $this->user = $user;
    }
    
    public function handle(): void {
        $userData = [
            'id' => $this->user->id,
            'name' => $this->user->name,
        ];
        Cache::put('user_' . $this->user->id, $userData, now()->addHours(24));
    }
}
```

**Job Dispatching:**
```php
public function createUser(array $data) {
    $user = $this->userRepository->createUser($data);
    event(new UserCreated($user));
    ProcessUserData::dispatch($user);  // Queue job
    return $user;
}
```

**Processing Queue:**
```bash
php artisan queue:work  # Start queue worker
# Job is added to queue (Redis)
# Worker picks up job
# Job::handle() executes
# Job completes or fails
```

**Benefits:**
- Non-blocking operations
- Long tasks (emails, processing) don't slow API
- Reliable delivery (jobs can be retried)
- Scalable (multiple workers)

### Q15: How is file upload handled securely in this project?
**Answer:**
The FileUploadService handles uploads with validation and storage strategies:

```php
class FileUploadService {
    public function uploadFile($user, $file, $description = null) {
        // Retrieve file metadata
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();       // Byte size
        $mimeType = $file->getMimeType();   // e.g., image/jpeg
        
        // Store file in organized directory
        $path = $file->store("uploads/user_{$user->id}", 'public');
        
        // Create database record with metadata
        $userFile = UserFile::create([
            'user_id' => $user->id,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'uploaded_by' => $user->id,
        ]);
        
        // Fire events
        event(new FileUploaded($userFile->id, $user->id, $fileName, $path));
        LogFileUpload::dispatch($userFile->id, $user->id, $fileName, $path);
        
        // Send notification
        Mail::to($user->email)->send(new FileUploadNotification($fileName, $user->name));
        
        return $userFile;
    }
}
```

**Validation (FileUploadRequest):**
```php
public function rules(): array {
    return [
        'file' => 'required|file|max:10240',  // 10MB limit
        'description' => 'nullable|string|max:500',
    ];
}
```

**Storage Organization:**
```
storage/uploads/
├── user_1/
│   ├── profile_picture.jpg
│   └── document.pdf
├── user_2/
│   └── resume.pdf
```

**Key Security Features:**
- File size limits (10MB max)
- User-specific directories
- Database record tracking
- File metadata storage
- Event logging via queued jobs

### Q16: How are relationships defined between models?
**Answer:**
Relationships define connections between entities. This project uses:

**One-to-One (User → UserProfile):**
```php
// User model
public function profile() {
    return $this->hasOne(UserProfile::class);
}

// UserProfile model
public function user() {
    return $this->belongsTo(User::class);
}

// Usage
$user = User::with('profile')->find(1);
$profileData = $user->profile;
```

**One-to-Many (User → UserFile):**
```php
// User model
public function files() {
    return $this->hasMany(UserFile::class);
}

// UserFile model
public function user() {
    return $this->belongsTo(User::class);
}

// Usage
$user = User::with('files')->find(1);
$allUserFiles = $user->files;
$filesCount = $user->files()->count();
```

**Benefits of Relationships:**
- Automatic eager loading prevents N+1 queries
- Clean syntax for accessing related data
- Built-in query optimization
- Supports nested relationships

### Q17: What is the purpose of database migrations?
**Answer:**
Migrations provide version control for database schema, allowing team collaboration and easy database recreation.

**Migration Example:**
```php
// 0001_01_01_000000_create_users_table.php
public function up(): void {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();          // Unique constraint
        $table->timestamp('email_verified_at')->nullable();
        $table->string('phone')->unique()->nullable();
        $table->string('password');
        $table->string('avatar')->nullable();
        $table->text('bio')->nullable();
        $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
        $table->rememberToken();
        $table->timestamps();                        // created_at, updated_at
        
        // Indexes for query performance
        $table->index('email');
        $table->index('phone');
        $table->index('status');
    });
}

public function down(): void {
    Schema::dropIfExists('users');
}
```

**Usage:**
```bash
php artisan migrate              # Run all pending migrations
php artisan migrate:rollback     # Reverse last batch
php artisan migrate:refresh      # Reset and re-run all
```

**Benefits:**
- Track schema changes in version control
- Easy to setup fresh database
- Collaborative development
- Development/Production consistency

### Q18: Explain HTTP status codes used in this project.
**Answer:**
HTTP status codes communicate operation results to clients.

| Code | Name | Usage | Example |
|------|------|-------|---------|
| 200 | OK | Successful GET/PUT | Retrieve users list |
| 201 | Created | Resource created | User registration successful |
| 204 | No Content | Delete successful | User deleted |
| 400 | Bad Request | Invalid input | Missing required fields |
| 401 | Unauthorized | Authentication required | No/invalid token |
| 403 | Forbidden | Authorization failed | User can't delete other users |
| 404 | Not Found | Resource doesn't exist | User ID not found |
| 422 | Unprocessable Entity | Validation error | Email already exists |
| 500 | Internal Server Error | Server error | Database connection failed |

**In Controller:**
```php
return response()->json([...], Response::HTTP_CREATED);      // 201
return response()->json([...], Response::HTTP_OK);            // 200
return response()->json([...], Response::HTTP_NOT_FOUND);     // 404
return response()->json([...], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
```

### Q19: How is error handling performed in controllers?
**Answer:**
Controllers wrap operations in try-catch blocks to handle errors gracefully:

```php
public function show($id) {
    try {
        // Attempt to retrieve user
        $user = $this->userService->getUserById($id);
        
        // Validate resource exists
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Success response
        return response()->json([
            'status' => true,
            'message' => 'User retrieved successfully',
            'data' => new UserResource($user),
        ], Response::HTTP_OK);
        
    } catch (\Exception $e) {
        // Handle unexpected errors
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
```

**Error Response Structure:**
```json
{
    "status": false,
    "message": "Error description here"
}
```

**Best Practices:**
- Always use try-catch blocks
- Return appropriate HTTP status codes
- Log errors for debugging
- Don't expose sensitive error details
- Provide meaningful error messages

### Q20: What are API routes and how are they defined?
**Answer:**
API routes define endpoints for HTTP requests. In this project:

```php
// routes/api.php

// Public routes (no authentication required)
Route::post('/auth/register', [UserController::class, 'store']);

// Protected routes (authentication required)
Route::middleware('auth:api')->group(function () {
    // User routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/search', [UserController::class, 'search']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    
    // File upload routes
    Route::post('/files/upload', [FileUploadController::class, 'store']);
    Route::get('/files', [FileUploadController::class, 'index']);
    Route::delete('/files/{fileId}', [FileUploadController::class, 'destroy']);
    Route::get('/files/{fileId}/download', [FileUploadController::class, 'download']);
});
```

**API Endpoints:**
| Method | Endpoint | Purpose | Auth |
|--------|----------|---------|------|
| POST | /auth/register | Create user | No |
| GET | /users | List users | Yes |
| GET | /users/{id} | Get user | Yes |
| PUT | /users/{id} | Update user | Yes |
| DELETE | /users/{id} | Delete user | Yes |
| POST | /files/upload | Upload file | Yes |
| GET | /files | List files | Yes |
| DELETE | /files/{id} | Delete file | Yes |

---

## ADVANCED LEVEL QUESTIONS

### Q21: Explain N+1 query problem and how this project prevents it.
**Answer:**
N+1 query problem occurs when loading related data causes excessive database queries.

**Problem Example:**
```php
// WITHOUT eager loading - N+1 queries
$users = User::all();  // 1 query
foreach ($users as $user) {
    echo $user->profile->bio;  // N additional queries
}
// Total: 1 + N queries
```

**Solution - Eager Loading:**
```php
// WITH eager loading - 2 queries
$users = User::with('profile', 'files')->paginate(15);
// Query 1: SELECT * FROM users LIMIT 15;
// Query 2: SELECT * FROM user_profiles WHERE user_id IN (1,2,3,...15);
// Query 3: SELECT * FROM user_files WHERE user_id IN (1,2,3,...15);
// Total: 3 queries regardless of user count

// In Repository
public function getAllUsers($page = 1, $perPage = 15) {
    return Cache::remember("users_page_{$page}_{$perPage}", now()->addHours(1), function () use ($perPage) {
        return User::with('profile', 'files')->paginate($perPage);  // Eager loading
    });
}
```

**Prevention Strategies:**
1. **Eager Loading with `with()`**
   ```php
   User::with('profile', 'files')->get();
   ```

2. **Lazy Eager Loading with `load()`**
   ```php
   $users = User::all();
   $users->load('profile', 'files');
   ```

3. **Caching** (implemented in repository)
   - Reduces queries by storing results
   - Cache key: `user_{id}`

### Q22: How would you implement role-based access control (RBAC)?
**Answer:**
RBAC restricts actions based on user roles. Implementation approach:

**Database Schema (additions):**
```php
// Migration: create_roles_table
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name');  // admin, moderator, user
    $table->timestamps();
});

// Migration: add_role_id_to_users_table
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('role_id')->default(3)->constrained('roles');  // Default: user
});
```

**Models:**
```php
class User extends Authenticatable {
    public function role() {
        return $this->belongsTo(Role::class);
    }
    
    public function hasRole($roleName) {
        return $this->role->name === $roleName;
    }
    
    public function isAdmin() {
        return $this->hasRole('admin');
    }
}
```

**Middleware (authorization):**
```php
// app/Http/Middleware/AdminOnly.php
class AdminOnly {
    public function handle(Request $request, Closure $next): Response {
        if (!$request->user('api')->isAdmin()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        return $next($request);
    }
}
```

**Route Protection:**
```php
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```

**Controller Implementation:**
```php
public function destroy($id) {
    // Check authorization
    if (auth('api')->user()->id !== $id && !auth('api')->user()->isAdmin()) {
        return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
    }
    
    return response()->json([...]);
}
```

### Q23: How would you implement soft deletes for users?
**Answer:**
Soft deletes mark records as deleted without removing them from database, enabling recovery.

**Implementation:**
```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->softDeletes();  // Adds deleted_at column
});

// Model
class User extends Authenticatable {
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}

// Query behaviors change
User::all();                    // Only active users
User::withTrashed()->all();     // Active + deleted
User::onlyTrashed()->all();     // Deleted only
$user->restore();               // Restore deleted user
$user->forceDelete();           // Permanently delete
```

**Controller Implementation:**
```php
public function destroy($id) {
    $user = User::find($id);
    $user->delete();  // Soft delete
    
    return response()->json(['status' => true, 'message' => 'User deleted']);
}

public function restore($id) {
    $user = User::withTrashed()->find($id);
    $user->restore();
    
    return response()->json(['status' => true, 'message' => 'User restored']);
}
```

**Benefits:**
- Data recovery capability
- Audit trail preservation
- Historical data retention
- Regulatory compliance
- No data loss risks

### Q24: How would you implement rate limiting for API endpoints?
**Answer:**
Rate limiting prevents abuse by restricting requests per user/IP.

**Implementation using Middleware:**
```php
// app/Http/Middleware/RateLimit.php
class RateLimit {
    public function handle(Request $request, Closure $next): Response {
        $key = $request->user('api')->id ?? $request->ip();
        $maxRequests = 60;
        $decayMinutes = 1;
        
        if (RateLimiter::tooManyAttempts($key, $maxRequests)) {
            return response()->json([
                'status' => false,
                'message' => 'Too many requests'
            ], 429);  // 429 Too Many Requests
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        return $next($request)->header(
            'X-RateLimit-Remaining',
            RateLimiter::remaining($key, $maxRequests)
        );
    }
}

// routes/api.php
Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

**Built-in Throttle Middleware:**
```php
Route::middleware('throttle:60,1')->group(function () {
    // 60 requests per 1 minute
    Route::get('/users', [UserController::class, 'index']);
});
```

**Benefits:**
- Prevents DoS attacks
- Controls resource usage
- Protects API stability
- Fair usage enforcement

### Q25: Explain database query optimization techniques.
**Answer:**
Query optimization improves performance and reduces server load.

**Techniques Used:**

1. **Indexing (in migrations):**
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();  // Unique = indexed
    $table->index('email');
    $table->index('phone');
    $table->index('status');
    // Indexed columns: faster searches, slower writes
});
```

2. **Eager Loading (prevent N+1):**
```php
User::with('profile', 'files')->paginate(15);
```

3. **Selective Column Loading:**
```php
User::select('id', 'name', 'email')->get();
// Not: User::select('*')->get();
```

4. **Query Caching:**
```php
Cache::remember("users_page_{$page}_{$perPage}", now()->addHours(1), function () {
    return User::paginate($perPage);
});
```

5. **Pagination (limit results):**
```php
User::paginate(15);  // Only get 15 per page
// Not: User::all();  // Could be millions
```

6. **Database Query Analysis:**
```bash
# Enable query logging
DB::listen(function($query) {
    echo $query->sql;  // View generated SQL
});
```

7. **Composite Indexes:**
```php
$table->index(['email', 'phone']);  // For WHERE email AND phone
```

### Q26: How would you handle file uploads with virus scanning?
**Answer:**
Implement antivirus scanning before storing uploaded files:

```php
class FileUploadService {
    public function uploadFile($user, $file, $description = null) {
        // Validate file
        if (!$this->isValidFile($file)) {
            throw new \Exception('Invalid file type or size');
        }
        
        // Scan for viruses (ClamAV integration)
        if (!$this->scanForViruses($file)) {
            throw new \Exception('File contains malware');
        }
        
        // Store file
        $path = $file->store("uploads/user_{$user->id}", 'public');
        
        // Create database record
        $userFile = UserFile::create([
            'user_id' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'scanned_at' => now(),
            'is_safe' => true,
        ]);
        
        return $userFile;
    }
    
    private function isValidFile($file) {
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024;  // 10MB
        
        return in_array($file->getMimeType(), $allowedMimes) 
            && $file->getSize() <= $maxSize;
    }
    
    private function scanForViruses($file) {
        // Use ClamAV or similar service
        $cl = new \ClamAV\Client();
        try {
            return $cl->scanFile($file->getRealPath());
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

**File Validation (Request):**
```php
public function rules(): array {
    return [
        'file' => 'required|file|max:10240|mimes:jpeg,png,pdf',
    ];
}
```

### Q27: How would you implement user search with full-text search?
**Answer:**
Implement search using Laravel Scout with search engines like Elasticsearch/Meilisearch:

**Scout Configuration:**
```php
// config/scout.php
'driver' => env('SCOUT_DRIVER', 'meilisearch'),

// .env
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=null
```

**Model Configuration:**
```php
class User extends Authenticatable {
    use Searchable;
    
    public function toSearchableArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'bio' => $this->bio,
        ];
    }
}
```

**Usage:**
```php
// Simple search
$users = User::search('john')->get();

// With filtering
$users = User::search('john')
    ->where('status', 'active')
    ->paginate(15);

// Advanced
$users = User::search('john')
    ->where('email', 'like', '%@gmail.com%')
    ->orderBy('created_at', 'desc')
    ->get();
```

**Repository Integration:**
```php
public function searchUsers($search) {
    return User::search($search)
        ->where('status', 'active')
        ->paginate(15);
}
```

**Benefits:**
- Full-text search capabilities
- Typo tolerance
- Relevance scoring
- Fast search performance
- Faceted search support

### Q28: Explain how to implement API versioning.
**Answer:**
API versioning allows supporting multiple API versions simultaneously for backward compatibility.

**Approach 1: URL Versioning**
```php
// routes/api.php
Route::prefix('api/v1')->group(function () {
    Route::get('/users', 'V1\UserController@index');
    Route::post('/users', 'V1\UserController@store');
});

Route::prefix('api/v2')->group(function () {
    Route::get('/users', 'V2\UserController@index');  // Different logic
    Route::post('/users', 'V2\UserController@store');
});

// Requests
// GET /api/v1/users     (V1 format)
// GET /api/v2/users     (V2 format with new fields)
```

**Approach 2: Header Versioning**
```php
// Middleware
class ApiVersion {
    public function handle(Request $request, Closure $next) {
        $version = $request->header('API-Version', '1');
        $request->version = $version;
        return $next($request);
    }
}

// Controller
public function index() {
    $version = request()->version;
    
    if ($version === '1') {
        return $this->indexV1();
    } elseif ($version === '2') {
        return $this->indexV2();
    }
}

// Request
// Headers: API-Version: 2
```

**Deprecation Strategy:**
```php
// Warn clients about version deprecation
return response()->json([
    'data' => [...],
    'meta' => [
        'api_version' => '1',
        'deprecation_warning' => 'API v1 deprecated. Use v2 instead.',
        'sunset_date' => '2025-12-31',
    ]
]);
```

### Q29: How would you implement API documentation generation?
**Answer:**
Generate API documentation automatically using tools like Laravel's built-in features or packages:

**Using OpenAPI/Swagger:**
```php
// Install Package
// composer require darkaonline/l5-swagger

// Generate Documentation
php artisan l5-swagger:generate

// Controller with documentation
class UserController extends Controller {
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="data", type="array")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index() {
        // Implementation
    }
}
```

**Generated Documentation:**
- Interactive Swagger UI at `/api/documentation`
- Auto-updated from code annotations
- Request/response examples
- Authentication requirements

**Alternative: Use comments**
```php
/**
 * Get all users with pagination
 * 
 * @param  int $page Pagination page number
 * @param  int $per_page Items per page (max 100)
 * @return \Illuminate\Http\JsonResponse
 * 
 * Response:
 * {
 *   "status": true,
 *   "data": [...],
 *   "pagination": {...}
 * }
 */
public function index() {
    // ...
}
```

### Q30: Explain how to implement content negotiation (JSON/XML responses).
**Answer:**
Content negotiation allows clients to request different response formats:

**Routes with Format Suffix:**
```php
Route::get('/users.json', [UserController::class, 'index']);
Route::get('/users.xml', [UserController::class, 'indexXml']);
```

**Accept Header Negotiation:**
```php
class ContentNegotiationController {
    public function index(Request $request) {
        $accept = $request->header('Accept');
        
        $users = User::paginate(15);
        
        if (strpos($accept, 'application/xml') !== false) {
            return $this->responseXml($users);
        }
        
        // Default: JSON
        return $this->responseJson($users);
    }
    
    private function responseJson($users) {
        return response()->json([
            'status' => true,
            'data' => UserResource::collection($users),
        ]);
    }
    
    private function responseXml($users) {
        $xml = new \SimpleXMLElement('<users/>');
        foreach ($users as $user) {
            $userNode = $xml->addChild('user');
            $userNode->addChild('id', $user->id);
            $userNode->addChild('name', $user->name);
            $userNode->addChild('email', $user->email);
        }
        return response($xml->asXML())->header('Content-Type', 'application/xml');
    }
}
```

**Client Requests:**
```bash
# Request JSON (default)
curl -H "Accept: application/json" http://localhost:8000/api/users

# Request XML
curl -H "Accept: application/xml" http://localhost:8000/api/users
```

---

## SECURITY-RELATED QUESTIONS

### Q31: What is SQL Injection and how does this project prevent it?
**Answer:**
SQL Injection is an attack where malicious SQL code is injected through user input to manipulate database queries.

**Vulnerable Code (Don't Use):**
```php
$email = request('email');
$user = DB::select("SELECT * FROM users WHERE email = '$email'");
// If email = ' OR '1'='1, returns all users
```

**Prevention in This Project:**
```php
// Method 1: Parameterized Queries (Eloquent - Default)
$user = User::where('email', request('email'))->first();
// Eloquent automatically escapes parameters

// Method 2: Query Bindings
$user = DB::select('SELECT * FROM users WHERE email = ?', [request('email')]);

// Method 3: Named Bindings
$user = DB::select('SELECT * FROM users WHERE email = :email', ['email' => request('email')]);

// Both methods prevent SQL injection by separating SQL from data
```

**Laravel's Protection:**
- Eloquent ORM uses parameterized queries internally
- Query Builder bindings escape data
- Automatic prepared statements

**Security Best Practice:**
```php
// ✓ Safe: Eloquent with validation
$validated = request()->validate(['email' => 'required|email']);
$user = User::where('email', $validated['email'])->first();

// ✓ Safe: Query Builder
$user = User::whereEmail(request('email'))->first();

// ✗ Dangerous: String concatenation
$user = DB::select("SELECT * FROM users WHERE email = '" . request('email') . "'");
```

### Q32: What is Cross-Site Request Forgery (CSRF) and how is it prevented?
**Answer:**
CSRF is an attack where an attacker tricks a user into making unintended requests from authenticated sessions.

**CSRF Attack Example:**
```
1. User logs into bank.com (authenticated session)
2. User visits malicious-site.com without logging out
3. malicious-site.com secretly sends:
   POST /bank/transfer HTTP/1.1
   From-Account: user's account
   To-Account: attacker's account
   Amount: 1000
```

**Prevention in Laravel:**
```php
// Middleware (automatically applied)
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    // Exclude CSRF for API if using tokens
];

// Form requests include CSRF token
<form method="POST" action="/api/users">
    @csrf  <!-- CSRF token field -->
    <input name="email" type="email">
</form>

// Or via headers for API
$token = csrf_token();
// Headers: X-CSRF-TOKEN: {$token}
```

**How CSRF Token Works:**
```
1. Server generates unique token
2. Token included in form/header
3. Client submits request with token
4. Server validates token matches session
5. Invalid token = request rejected
```

**API Protection (Token-Based):**
```php
// Passport tokens are automatically validated
Route::middleware('auth:api')->post('/users/{id}', [UserController::class, 'update']);

// Token in Authorization header
Headers: Authorization: Bearer {access_token}
```

**Best Practices:**
- Enable CSRF for all state-changing operations
- Validate tokens on sensitive endpoints
- Rotate tokens periodically
- Use SameSite cookie attribute

### Q33: What is Cross-Site Scripting (XSS) and how does this project prevent it?
**Answer:**
XSS is an attack where malicious JavaScript is injected into web pages to steal user data or perform unauthorized actions.

**XSS Attack Example:**
```html
<!-- Vulnerable code -->
<p>{{ $bio }}</p>

<!-- If bio = <script>fetch('/steal-data')</script> -->
<!-- Script executes in user's browser -->
```

**Prevention in This Project:**

1. **HTML Escaping (Default in Blade):**
```blade
{!! $bio !!}              <!-- Raw output - VULNERABLE -->
{{ $bio }}                <!-- Properly escaped - SAFE -->
<!-- Converts <script> to &lt;script&gt; -->
```

2. **JSON Resources (API):**
```php
class UserResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'bio' => $this->bio,  // Automatically escaped by json_encode
        ];
    }
}

// Returns: {"bio": "Safe text"}
// If bio contains script tags, they're encoded as strings, not executed
```

3. **Request Validation:**
```php
class StoreUserRequest extends FormRequest {
    public function rules(): array {
        return [
            'bio' => 'nullable|string|max:500',
        ];
    }
}
// Validates input as string, rejects scripts
```

4. **Content Security Policy (CSP):**
```php
// Add to middleware or header
header("Content-Security-Policy: default-src 'self'");
// Disallows inline scripts, only allows same-origin
```

**Storage in Database:**
```php
// Input: <script>alert('xss')</script>
// Stored as string, not code
// When retrieved and displayed:
{{ $user->bio }}  // Renders as: &lt;script&gt;alert('xss')&lt;/script&gt;
```

### Q34: What is password hashing and why is it important?
**Answer:**
Password hashing converts passwords into irreversible hashes for secure storage, preventing exposure if database is compromised.

**Implementation in Project:**
```php
// User registration
class StoreUserRequest {
    public function rules(): array {
        return [
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}

// Service layer
class UserService {
    public function createUser(array $data) {
        // Hash password before storage
        $data['password'] = Hash::make($data['password']);
        
        $user = $this->userRepository->createUser($data);
        return $user;
    }
}

// User model (hidden from responses)
class User extends Authenticatable {
    protected $hidden = [
        'password',           // Password never exposed
        'remember_token',
    ];
}
```

**Hashing Example:**
```php
$password = "SecurePassword123";

// Hash creation
$hashed = Hash::make($password);
// Result: $2y$10$abcd1234...encrypted_value...

// Hash verification (during login)
if (Hash::check($password, $hashed)) {
    // Passwords match
}

// Same password hashes differently each time
Hash::make("password") !== Hash::make("password")  // true
Hash::check("password", $hashed) === true          // true
```

**Why Hashing is Critical:**
- Passwords never stored in plain text
- Even if database is breached, passwords are safe
- One-way function: can't decrypt hashes
- Salting prevents rainbow table attacks
- Bcrypt makes brute-force attacks slow

**Best Practices:**
- Always hash passwords with `Hash::make()`
- Never store plain passwords
- Use strong hashing algorithms (Bcrypt, Argon2)
- Implement password strength requirements
- Enforce password expiration policies

### Q35: What is the principle of least privilege in API design?
**Answer:**
Principle of least privilege means users should have minimum permissions needed to perform tasks, reducing damage from compromised accounts.

**Implementation in Project:**

1. **Role-Based Access Control:**
```php
// Only allow users to update their own profile
public function update($id, UpdateUserRequest $request) {
    // Check authorization
    $currentUser = auth('api')->user();
    if ($currentUser->id !== $id && !$currentUser->isAdmin()) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
    
    // Proceed with update
    $user = $this->userService->updateUser($id, $request->validated());
    return response()->json([...]);
}
```

2. **File Access Control:**
```php
// Users can only access their own files
public function deleteFile($fileId) {
    $userId = auth('api')->user()->id;
    
    // Verify ownership
    $file = UserFile::where('id', $fileId)
                    ->where('user_id', $userId)  // Ownership check
                    ->first();
    
    if (!$file) {
        return response()->json(['status' => false, 'message' => 'Not found'], 404);
    }
    
    $file->delete();
    return response()->json(['status' => true]);
}
```

3. **API Token Scopes:**
```php
// Create token with specific scopes
$token = $user->createToken('App', ['read', 'write'])->plainTextToken;

// Middleware checks scopes
Route::middleware(['auth:api', 'scope:write'])->post('/users/{id}', [
    UserController::class, 'update'
]);
```

4. **Restricted Endpoints:**
```php
// Admin-only operations
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::delete('/users/{id}', [UserController::class, 'destroy']);  // Destructive
    Route::get('/analytics', [AnalyticsController::class, 'index']);    // Sensitive
});

// User operations
Route::middleware('auth:api')->group(function () {
    Route::put('/users/{id}', [UserController::class, 'update']);      // Own profile
    Route::get('/files', [FileController::class, 'index']);             // Own files
});
```

**Benefits:**
- Limits damage from compromised accounts
- Prevents accidental data access
- Improves security posture
- Meets compliance requirements
- Reduces liability

### Q36: What is authentication vs authorization and how are they different?
**Answer:**
Authentication verifies user identity, while authorization determines what authenticated users can do.

**Authentication (Who are you?):**
```php
// User provides credentials
POST /auth/register
{
    "email": "user@example.com",
    "password": "secure123"
}

// Passport validates and issues token
Response: {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 3600
}

// Authentication check
Route::post('/users', [UserController::class, 'store']);  // No auth required

// Verify authentication
$user = auth('api')->user();  // Returns user if authenticated
```

**Authorization (What can you do?):**
```php
// Determine access rights
public function destroy($id) {
    $currentUser = auth('api')->user();
    
    // Authorization check
    if ($currentUser->id !== $id && !$currentUser->isAdmin()) {
        return response()->json([
            'status' => false,
            'message' => 'Forbidden'  // 403 Forbidden
        ], 403);
    }
}

// Policy-based authorization
Route::middleware(['auth:api'])->group(function () {
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```

**Comparison:**
| Aspect | Authentication | Authorization |
|--------|----------------|---------------|
| Purpose | Verify identity | Grant permissions |
| Question | Who are you? | What can you do? |
| Implementation | Tokens, passwords | Roles, policies |
| Failure Code | 401 Unauthorized | 403 Forbidden |
| Example | Login check | Admin-only route |

### Q37: How should API keys be managed securely?
**Answer:**
API keys must be stored securely and never exposed to prevent unauthorized access.

**Secure API Key Management:**

1. **Generate Secure Keys:**
```php
// Generate random, cryptographically-secure token
$apiKey = bin2hex(random_bytes(32));  // 64 hex characters
// Store hash, not key itself
$hashedKey = hash('sha256', $apiKey);
```

2. **Secure Storage (Database):**
```php
Schema::create('api_keys', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id');
    $table->string('key_hash')->unique();  // Hash, not plain text
    $table->string('name');                // e.g., "Mobile App"
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});

// When verifying
$key = request()->header('X-API-Key');
$hashedKey = hash('sha256', $key);
$apiKey = ApiKey::where('key_hash', $hashedKey)->first();
```

3. **Never Expose Keys:**
```php
// ✗ Don't do this
return response()->json([
    'api_key' => $apiKey,  // Exposed in response
]);

// ✓ Return only on creation
$key = bin2hex(random_bytes(32));
return response()->json([
    'api_key' => $key,  // Show once on creation
    'message' => 'Store this key securely. You won\'t see it again.'
]);
```

4. **Environment Variables:**
```bash
# .env
OAUTH_KEY=ey9n30w0e9...  # Third-party keys
STRIPE_SECRET_KEY=sk_...

# .env.local (excluded from git)
# Never commit .env to version control
```

5. **Rotation Policy:**
```php
// Invalidate old keys
$apiKey = ApiKey::where('created_at', '<', now()->subMonths(6))->delete();

// Support key versioning
Route::middleware('api_key:v2')->group(function () {
    // Uses v2 key format
});
```

6. **Audit Logging:**
```php
// Log API key usage
ApiKeyLog::create([
    'api_key_id' => $key->id,
    'endpoint' => request()->path(),
    'ip_address' => request()->ip(),
    'timestamp' => now(),
    'status' => 'success',
]);
```

**Best Practices:**
- Store only hashed keys in database
- Generate cryptographically-secure keys
- Rotate keys periodically
- Never log full keys
- Use short expiration times
- Implement key usage limits

### Q38: What are common API security vulnerabilities and how to prevent them?
**Answer:**
Common vulnerabilities and mitigation strategies:

**1. Injection Attacks (SQL, Command, etc.)**
```php
// ✗ Vulnerable
$user = DB::select("SELECT * FROM users WHERE id = " . request('id'));

// ✓ Secure
$user = User::find(request('id'));
```

**2. Broken Authentication**
```php
// ✗ Weak password requirement
'password' => 'min:3'

// ✓ Strong requirement
'password' => 'required|min:8|regex:/[A-Z]/|regex:/[0-9]/'

// ✗ Token not expiring
// ✓ Set token expiration
$token->expires_in = 3600;  // 1 hour
```

**3. Sensitive Data Exposure**
```php
// ✗ Exposing password and sensitive fields
class UserResource {
    'password' => $this->password,
    'credit_card' => $this->credit_card,
}

// ✓ Hide sensitive fields
class UserResource {
    'id' => $this->id,
    'name' => $this->name,
    // Password and credit_card not included
}
```

**4. XML External Entity (XXE)**
```php
// ✗ Disable external entity loading
libxml_disable_entity_loader(true);

// ✓ Safe XML parsing
$xml = simplexml_load_string($content, null, LIBXML_DOITALL | LIBXML_NOENT);
```

**5. Broken Access Control**
```php
// ✗ Trusting user-provided ID without verification
public function destroy($id) {
    User::find($id)->delete();  // Any user can delete anyone
}

// ✓ Verify authorization
public function destroy($id) {
    if (auth('api')->user()->id !== $id && !auth('api')->user()->isAdmin()) {
        return response()->json(['status' => false], 403);
    }
    User::find($id)->delete();
}
```

**6. Missing HTTPS**
```php
// ✓ Force HTTPS in AppServiceProvider
public function boot() {
    if ($this->app->environment('production')) {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}

// .env
APP_URL=https://api.example.com
```

**7. Rate Limiting**
```php
// ✓ Prevent brute force
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});
```

**8. NoSQL Injection (if using MongoDB)**
```php
// ✗ Vulnerable
User::where('email', {'$ne': null})->get();

// ✓ Safe
User::where('email', '!=', null)->get();
```

### Q39: How should you handle sensitive error information in production?
**Answer:**
In production, don't expose detailed error information that helps attackers.

**Development (Debug Mode):**
```php
// .env
APP_DEBUG=true   // Shows detailed errors with stack traces
```

**Production (Secure):**
```php
// .env
APP_DEBUG=false  // Shows generic error messages

// Exception handler
public function render($request, Throwable $exception) {
    // Log detailed error (for debugging)
    Log::error($exception);
    
    // Return generic response to client
    if ($this->isHttpException($exception)) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred'  // Don't expose details
        ], $exception->getStatusCode());
    }
    
    // Generic 500 error
    return response()->json([
        'status' => false,
        'message' => 'Internal server error'
    ], 500);
}
```

**Custom Error Responses:**
```php
// ✗ Exposes database structure
{
    "error": "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'invalid_col' in 'where clause'",
    "trace": [...]
}

// ✓ Generic error message
{
    "status": false,
    "message": "An error occurred processing your request"
}

// ✓ Client-logged error
{
    "status": false,
    "message": "Invalid request",
    "error_id": "ERR-f47ac10b-58cc-4372-a567-0e02b2c3d479"
}
// Client can reference error ID for support
```

**Logging Sensitive Errors:**
```php
// Log full error details
Log::error('Database error', [
    'exception' => get_class($exception),
    'message' => $exception->getMessage(),
    'trace' => $exception->getTraceAsString(),
    'user_id' => auth('api')->id(),
    'timestamp' => now(),
]);

// Then show generic message to user
return response()->json(['status' => false, 'message' => 'Error occurred'], 500);
```

**Best Practices:**
- Never expose stack traces in production
- Log detailed errors on server side
- Return generic error messages to clients
- Use error IDs for tracking
- Monitor and alert on critical errors

### Q40: How should you implement secure file uploads?
**Answer:**
Implement multiple layers of file upload security:

**1. Validation (Request Level):**
```php
class FileUploadRequest extends FormRequest {
    public function rules(): array {
        return [
            'file' => 'required|file|max:10240',  // 10MB max
                     '|mimes:jpeg,png,pdf',         // Only safe types
            'description' => 'nullable|string|max:500',
        ];
    }
}
```

**2. Type Verification (MIME Type):**
```php
public function uploadFile($user, $file, $description = null) {
    $allowedMimes = [
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        'application/pdf' => '.pdf',
    ];
    
    $mimeType = $file->getMimeType();
    
    if (!isset($allowedMimes[$mimeType])) {
        throw new \Exception('File type not allowed');
    }
    
    // Verify actual content matches MIME type
    $extension = $file->extension();
    if (!in_array('.' . $extension, $allowedMimes)) {
        throw new \Exception('File extension mismatch');
    }
}
```

**3. Secure Storage:**
```php
// Store outside web root for sensitive files
$path = $file->store("uploads/user_{$user->id}", 'private');

// Or use cloud storage
$path = Storage::disk('s3')->put("user_{$user->id}", $file);

// Never store in web-accessible directory if sensitive
// storage/uploads/ is NOT accessible directly
```

**4. File Size Limits:**
```php
'file' => 'required|file|max:10240',  // 10MB in KB

// Also check at application level
if ($file->getSize() > 10 * 1024 * 1024) {
    throw new \Exception('File too large');
}
```

**5. Virus Scanning:**
```php
private function scanForViruses($file) {
    // Use ClamAV API
    $cl = new \ClamAV\Client('127.0.0.1', 3310);
    try {
        $result = $cl->scanFile($file->getRealPath());
        return $result === 'OK';
    } catch (\Exception $e) {
        Log::error('Virus scan failed', ['error' => $e->getMessage()]);
        return false;  // Reject if scan fails
    }
}
```

**6. Secure Download:**
```php
public function downloadFile($fileId, $userId) {
    // Verify ownership
    $file = UserFile::where('id', $fileId)
                    ->where('user_id', $userId)
                    ->first();
    
    if (!$file) {
        return response()->json(['status' => false], 404);
    }
    
    // Use stream with appropriate headers
    return Storage::disk('private')->download(
        $file->file_path,
        $file->file_name,  // Client filename
        [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $file->file_name . '"',
        ]
    );
}
```

**7. Metadata Sanitization:**
```php
// Remove dangerous metadata from images
$image = Image::make($file->getRealPath());
// Remove EXIF data
$image->save($destination, 80);  // Re-encode removes metadata

// Store safe metadata
UserFile::create([
    'file_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
    'file_size' => $file->getSize(),
    'mime_type' => $file->getMimeType(),
    'uploaded_at' => now(),
    'is_scanned' => true,
]);
```

**Security Checklist:**
- ✓ Validate file types and extensions
- ✓ Enforce file size limits
- ✓ Store outside web root if sensitive
- ✓ Rename files to prevent direct access
- ✓ Perform virus scanning
- ✓ Remove dangerous metadata
- ✓ Verify user ownership on download
- ✓ Use HTTPS for all file transfers
- ✓ Log all uploads for audit trail
- ✓ Implement rate limiting for uploads

---

## Additional Advanced Security Topics

### Q41: What is CORS (Cross-Origin Resource Sharing) and how should it be configured?
**Answer:**
CORS allows controlled access to API resources from different domains.

**Configuration:**
```php
// config/cors.php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['https://app.example.com'],
'allowed_origins_patterns' => ['https://*.example.com'],
'allowed_headers' => ['*'],
'exposed_headers' => ['Authorization'],
'max_age' => 0,
'supports_credentials' => true,
```

**Securing CORS:**
```php
// ✗ Insecure: Allow all origins
'allowed_origins' => ['*'],

// ✓ Secure: Whitelist specific domains
'allowed_origins' => [
    'https://app.example.com',
    'https://admin.example.com',
],

// ✓ Pattern-based
'allowed_origins_patterns' => ['^https://.*\.example\.com$'],
```

### Q42: How should you implement HTTPS/TLS in production?
**Answer:**
HTTPS encrypts data in transit, protecting against man-in-the-middle attacks.

**Implementation:**
```php
// App Service Provider
public function boot() {
    if ($this->app->environment('production')) {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}

// Force HTTPS middleware
class ForceHttps {
    public function handle($request, Closure $next) {
        if (!$request->secure() && config('app.env') === 'production') {
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);
    }
}

// Nginx configuration
server {
    listen 443 ssl http2;
    server_name api.example.com;
    
    ssl_certificate /etc/ssl/certs/api.example.com.crt;
    ssl_certificate_key /etc/ssl/private/api.example.com.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
}
```

**Security Headers:**
```php
// Add to middleware
return $next($request)
    ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
    ->header('X-Content-Type-Options', 'nosniff')
    ->header('X-Frame-Options', 'DENY')
    ->header('X-XSS-Protection', '1; mode=block')
    ->header('Referrer-Policy', 'strict-origin-when-cross-origin');
```

### Q43: How should you implement audit logging for security events?
**Answer:**
Audit logs track security-sensitive events for compliance and forensics.

**Implementation:**
```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable();
    $table->string('action');              // 'user_created', 'file_uploaded'
    $table->string('entity_type');         // 'User', 'File'
    $table->string('entity_id');
    $table->json('changes')->nullable();   // What changed
    $table->string('ip_address');
    $table->string('user_agent');
    $table->timestamp('timestamp')->useCurrent();
});

// Logging function
public function logAudit($action, $entityType, $entityId, $changes = null) {
    AuditLog::create([
        'user_id' => auth('api')->id(),
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'changes' => $changes,
        'ip_address' => request()->ip(),
        'user_agent' => request()->header('User-Agent'),
    ]);
}

// Usage
public function deleteUser($id) {
    $user = User::find($id);
    $this->logAudit('user_deleted', 'User', $id, [
        'email' => $user->email,
        'deleted_at' => now(),
    ]);
    $user->delete();
}
```

### Q44: How should secrets be managed in CI/CD pipelines?
**Answer:**
Secrets (keys, tokens, passwords) must be securely managed without exposure.

**Best Practices:**
```bash
# ✗ Never hardcode secrets
API_KEY="abc123def456"

# ✓ Use environment variables
export API_KEY=$(aws secretsmanager get-secret-value --secret-id api-key)

# ✓ Use secret management tools
# GitHub Secrets
# GitLab CI/CD Variables
# HashiCorp Vault
# AWS Secrets Manager

# CI/CD Pipeline (.github/workflows/deploy.yml)
name: Deploy
on: [push]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Deploy
        env:
          API_KEY: ${{ secrets.API_KEY }}
          DATABASE_PASSWORD: ${{ secrets.DB_PASSWORD }}
        run: ./deploy.sh
```

### Q45: What is the importance of dependency management and security?
**Answer:**
Keeping dependencies updated prevents exploitation of known vulnerabilities.

**Implementation:**
```bash
# Check for vulnerabilities
composer audit

# Update dependencies safely
composer update

# Check specific package
composer show symfony/http-foundation

# Lock file prevents unexpected updates
# Use composer.lock for reproducible installs
```

**Security Best Practices:**
```php
// composer.json
{
    "require": {
        "laravel/framework": "^10.0",
        "laravel/passport": "^12.0",  // Specify version constraints
    }
}

// Regular updates
composer update        // Update to latest compatible
composer update -interactive  // Selective updates

// Monitor dependencies
# Enable Dependabot on GitHub
# Use snyk.io for vulnerability scanning
```

---

## Summary Table

| Concept | Purpose | Security Level |
|---------|---------|-----------------|
| Authentication | Verify user identity | High |
| Authorization | Control user actions | High |
| Encryption | Protect data in transit | High |
| Hashing | Secure password storage | High |
| Validation | Prevent invalid data | Medium |
| SQL Injection Prevention | Protect database queries | High |
| XSS Prevention | Prevent script injection | High |
| CSRF Protection | Prevent unauthorized requests | High |
| Rate Limiting | Prevent brute force | Medium |
| CORS Configuration | Control cross-origin access | Medium |
| Audit Logging | Track security events | Medium |

---

## Conclusion

This comprehensive guide covers Laravel fundamentals to advanced concepts with strong emphasis on security. Each question builds on previous knowledge, providing a complete picture of building secure, scalable APIs with Laravel.

**Key Takeaways:**
- Security should be prioritized throughout development
- Use Laravel's built-in security features (middleware, validation, hashing)
- Implement least privilege principle
- Validate all input and sanitize output
- Use HTTPS in production
- Monitor and log security events
- Keep dependencies updated
- Regular security audits and penetration testing
