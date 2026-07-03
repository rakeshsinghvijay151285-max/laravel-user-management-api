# API Documentation

## User Management API - Complete Reference

### Base URL
```
http://localhost:8000/api
```

### Authentication
All endpoints except `/auth/register` require Bearer token authentication.
```
Authorization: Bearer {access_token}
```

### Response Format
All responses are in JSON format with the following structure:

**Success Response:**
```json
{
  "status": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "status": false,
  "message": "Error description"
}
```

---

## Authentication Endpoints

### 1. Register User
**Endpoint:** `POST /auth/register`
**Authentication:** Not required
**Description:** Create a new user account

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "password": "password@123",
  "password_confirmation": "password@123",
  "avatar": "(file upload)",
  "bio": "My bio"
}
```

**Response (201):**
```json
{
  "status": true,
  "message": "User created successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "avatar": "avatars/file.jpg",
    "bio": "My bio",
    "status": "active",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Validation Errors:**
- `name` - Required, max 255 characters
- `email` - Required, valid email, unique
- `phone` - Optional, max 20 characters, unique
- `password` - Required, min 8 characters, must be confirmed
- `avatar` - Optional, image file, max 2MB

---

## User Endpoints

### 2. List All Users
**Endpoint:** `GET /users`
**Authentication:** Required
**Description:** Get paginated list of all users

**Query Parameters:**
```
page: integer (default: 1)
per_page: integer (default: 15, max: 100)
```

**Example:**
```
GET /users?page=1&per_page=20
```

**Response (200):**
```json
{
  "status": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "avatar": "avatars/file.jpg",
      "bio": "My bio",
      "status": "active",
      "files_count": 5,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "total": 50,
    "per_page": 20,
    "current_page": 1,
    "last_page": 3
  }
}
```

### 3. Get User by ID
**Endpoint:** `GET /users/{id}`
**Authentication:** Required
**Description:** Get detailed information about a specific user

**Path Parameters:**
```
id: integer (required) - User ID
```

**Response (200):**
```json
{
  "status": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "avatar": "avatars/file.jpg",
    "bio": "My bio",
    "status": "active",
    "email_verified_at": null,
    "profile": {
      "date_of_birth": "1990-01-15",
      "address": "123 Main St",
      "city": "New York",
      "state": "NY",
      "country": "USA",
      "postal_code": "10001"
    },
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Error Response (404):**
```json
{
  "status": false,
  "message": "User not found"
}
```

### 4. Update User
**Endpoint:** `PUT /users/{id}`
**Authentication:** Required
**Description:** Update user information

**Path Parameters:**
```
id: integer (required) - User ID
```

**Request Body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "+9876543210",
  "password": "newpassword@123",
  "password_confirmation": "newpassword@123",
  "avatar": "(file upload)",
  "bio": "Updated bio",
  "status": "active"
}
```

**Response (200):**
```json
{
  "status": true,
  "message": "User updated successfully",
  "data": { ... }
}
```

**Available Statuses:**
- `active` - User is active
- `inactive` - User is inactive
- `suspended` - User is suspended

### 5. Delete User
**Endpoint:** `DELETE /users/{id}`
**Authentication:** Required
**Description:** Delete a user account

**Path Parameters:**
```
id: integer (required) - User ID
```

**Response (200):**
```json
{
  "status": true,
  "message": "User deleted successfully"
}
```

### 6. Search Users
**Endpoint:** `GET /users/search?q=query`
**Authentication:** Required
**Description:** Search users by name, email, or phone

**Query Parameters:**
```
q: string (required) - Search query
```

**Example:**
```
GET /users/search?q=john
```

**Response (200):**
```json
{
  "status": true,
  "message": "Search results",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "avatar": "avatars/file.jpg",
      "bio": "My bio",
      "status": "active",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

---

## File Upload Endpoints

### 7. Upload File
**Endpoint:** `POST /files/upload`
**Authentication:** Required
**Content-Type:** `multipart/form-data`
**Description:** Upload a file for the authenticated user

**Form Parameters:**
```
file: binary (required) - File to upload (max 10MB)
description: string (optional) - File description
```

**Response (201):**
```json
{
  "status": true,
  "message": "File uploaded successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "file_name": "document.pdf",
    "file_path": "uploads/user_1/document.pdf",
    "file_size": 1024000,
    "mime_type": "application/pdf",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Validation Errors:**
- `file` - Required, max 10MB
- File type validation (must be valid file)

### 8. Get User Files
**Endpoint:** `GET /files`
**Authentication:** Required
**Description:** Get all files uploaded by the authenticated user

**Query Parameters:**
```
page: integer (default: 1)
per_page: integer (default: 15)
```

**Response (200):**
```json
{
  "status": true,
  "message": "Files retrieved successfully",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "file_name": "document.pdf",
      "file_path": "uploads/user_1/document.pdf",
      "file_size": 1024000,
      "mime_type": "application/pdf",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

### 9. Download File
**Endpoint:** `GET /files/{fileId}/download`
**Authentication:** Required
**Description:** Download a file uploaded by the user

**Path Parameters:**
```
fileId: integer (required) - File ID
```

**Response (200):**
- Returns the file as binary attachment

**Error Response (404):**
```json
{
  "status": false,
  "message": "File not found"
}
```

### 10. Delete File
**Endpoint:** `DELETE /files/{fileId}`
**Authentication:** Required
**Description:** Delete a file

**Path Parameters:**
```
fileId: integer (required) - File ID
```

**Response (200):**
```json
{
  "status": true,
  "message": "File deleted successfully"
}
```

---

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Successful GET, PUT request |
| 201 | Created | Successful POST request |
| 400 | Bad Request | Validation error |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 500 | Internal Server Error | Server error |

---

## Rate Limiting

Currently no rate limiting is implemented. Add the following to implement:

```php
Route::middleware('throttle:60,1')->group(function () {
    // Your routes here
});
```

---

## CORS Configuration

Add to `config/cors.php` for production:
```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['https://yourdomain.com'],
```

---

## Webhook Events (Kafka)

The following events are published to Kafka:
- `user_created` - User registration
- `user_updated` - User information updated
- `user_deleted` - User deleted
- `file_uploaded` - File uploaded

Payloads are published to the `user-events` Kafka topic.

---

## Postman Collection

Import the following into Postman for testing:

```json
{
  "info": {
    "name": "User Management API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Register",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/auth/register",
        "body": { ... }
      }
    }
  ]
}
```

---

## Development Notes

- All timestamps are in ISO 8601 format (UTC)
- All file paths are relative to storage/app/public
- Pagination uses cursor-based or offset-based approach
- Cache keys follow: `{resource}_{id}` pattern
- Events are fired asynchronously via queue jobs

---

**Version:** 1.0.0  
**Last Updated:** 2024-01-15  
**Status:** Active
