# Product Management API

A Laravel REST API for managing products with authentication, image uploads, filtering, pagination, and user ownership.

## Features

- User Registration & Authentication (Laravel Passport)
- Product CRUD Operations
- Multiple Image Upload per Product
- Image Resizing (800x800px max)
- Product Filtering (price range, category, availability)
- Pagination & Sorting
- User Ownership (users can only manage their own products)

## Installation

### Step 1: Install Dependencies

```bash
composer install
```

### Step 2: Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Database Configuration

**For SQLite (Default):**
```bash
touch database/database.sqlite
```

Update `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=C:\laragon\www\testtask\database\database.sqlite
```

**For MySQL:**
Update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 4: Install Passport

```bash
php artisan passport:install
```

This creates OAuth tables and generates encryption keys. You'll see output like:
```
Personal access client created successfully.
Client ID: 1
```

**If personal access client is not created, run:**
```bash
php artisan passport:client --personal
```
When prompted, press Enter for default name and enter `users` for user provider.

### Step 5: Run Migrations

```bash
php artisan migrate:fresh --seed
```

**Note:** This drops all tables. After running this, reinstall Passport:
```bash
php artisan passport:install
```

### Step 6: Create Storage Link

```bash
php artisan storage:link
```

### Step 7: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Step 8: Start Server

```bash
php artisan serve
```

API will be available at: `http://localhost:8000`

## Using the API

### 1. Register a User

```bash
POST http://localhost:8000/api/register
Content-Type: multipart/form-data

name: Admin User
email: admin@gmail.com
password: admin123
```

**Response:**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {...},
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "Bearer"
    }
}
```

### 2. Login

```bash
POST http://localhost:8000/api/login
Content-Type: multipart/form-data

email: admin@gmail.com
password: admin123
```

Save the `access_token` from the response.

### 3. Create Product

```bash
POST http://localhost:8000/api/products
Authorization: Bearer {access_token}
Content-Type: multipart/form-data

name: Laptop
description: High-performance laptop
price: 999.99
stock: 50
category_id: 1
images[]: [file1.jpg]
images[]: [file2.jpg]
```

### 4. List Products

```bash
GET http://localhost:8000/api/products?page=1&limit=15&sort_by=price&sort_order=asc
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 15, max: 100)
- `sort_by` - Field to sort (name, price, created_at, stock)
- `sort_order` - Sort direction (asc, desc)
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `category` - Category ID filter
- `availability` - Filter by stock (in_stock, out_of_stock)

### 5. Get Single Product

```bash
GET http://localhost:8000/api/products/1
```

### 6. Update Product

```bash
POST http://localhost:8000/api/products/1
Authorization: Bearer {access_token}
Content-Type: multipart/form-data

name: Updated Laptop
price: 1099.99
images[]: [file1.jpg]
```

**Note:** 
- Only product owner can update
- All fields are optional (partial updates)
- Images replace existing ones (not appended)

### 7. Delete Product

```bash
DELETE http://localhost:8000/api/products/1
Authorization: Bearer {access_token}
```

**Note:** Only product owner can delete.

### 8. Upload Images

```bash
POST http://localhost:8000/api/products/1/upload-image
Authorization: Bearer {access_token}
Content-Type: multipart/form-data

images[]: [file1.jpg]
images[]: [file2.jpg]
```

**Note:** 
- Only product owner can upload
- Images are appended (not replaced)

### 9. Get Categories

```bash
GET http://localhost:8000/api/categories
```

## Postman Collection

Import the Postman collection for easy testing:

1. Import `postman/Product_Management_API.postman_collection.json`
2. Import `postman/Product_Management_API.postman_environment.json`
3. Set `base_url` in environment: `http://localhost:8000`
4. Register/Login to get access token
5. Manually add the access token to environment variable `access_token`

## API Response Format

**Success:**
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {...}
}
```

**Error:**
```json
{
    "success": false,
    "message": "Error message",
    "error": "Detailed error (development mode only)"
}
```

## Important Notes

- **User Ownership:** Products are assigned to the user who creates them. Users can only update/delete their own products.
- **Image Updates:** When updating a product with images, all existing images are replaced.
- **Image Upload:** Using the upload-image endpoint appends images (does not replace).
- **Authentication:** Include `Authorization: Bearer {access_token}` header for protected endpoints.
- **Request Format:** Use `multipart/form-data` for all requests (including file uploads).

## Troubleshooting

### "Personal access client not found" Error

Run:
```bash
php artisan passport:install
```

Or create manually:
```bash
php artisan passport:client --personal
```
Enter `users` when prompted for user provider.

### Storage Link Not Working

```bash
php artisan storage:link
```

### Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Requirements

- PHP >= 8.2
- Composer
- Laravel 12.x
- Database (SQLite/MySQL/PostgreSQL)
- GD Library (for image processing)

## Dependencies

- Laravel Passport ^13.4 - OAuth2 authentication
- Intervention Image ^3.11 - Image manipulation
