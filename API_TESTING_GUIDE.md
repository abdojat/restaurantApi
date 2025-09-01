# Restaurant Management System - API Testing Guide

This guide provides examples of how to test the various API endpoints using tools like Postman, cURL, or any HTTP client.

## Base URL
```
http://localhost:8000/api
```

## 1. Authentication

### Register a new customer
```http
POST /auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "phone_number": "+1234567890",
    "password": "Password123!",
    "password_confirmation": "Password123!"
}
```

**Expected Response (201):**
```json
{
    "message": "Registered successfully.",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone_number": "+1234567890",
        "role_id": 4,
        "created_at": "2025-08-18T21:25:13.000000Z"
    },
    "token": "1|abc123..."
}
```

### Login
```http
POST /auth/login
Content-Type: application/json

{
    "email": "admin@restaurant.com",
    "password": "admin123"
}
```

**Expected Response (200):**
```json
{
    "message": "Logged in successfully.",
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@restaurant.com",
        "role_id": 1
    },
    "token": "1|abc123..."
}
```

## 2. Public Menu Endpoints

### Get complete menu
```http
GET /menu
```

### Get menu categories
```http
GET /menu/categories
```

### Search dishes
```http
GET /menu/search?query=salmon&vegetarian=true
```

## 3. Admin Endpoints (Requires admin token)

### Get all users
```http
GET /admin/users
Authorization: Bearer {admin_token}
```

### Create a new user
```http
POST /admin/users
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "name": "Manager User",
    "email": "manager2@restaurant.com",
    "phone_number": "+1234567891",
    "password": "Manager123!",
    "password_confirmation": "Manager123!",
    "role_id": 2
}
```

## 4. Manager Endpoints (Requires admin or manager token)

### Create a table
```http
POST /manager/tables
Authorization: Bearer {manager_token}
Content-Type: application/json

{
    "name": "Table 5",
    "capacity": 6,
    "description": "Corner table with view",
    "is_active": true
}
```

### Create a category
```http
POST /manager/categories
Authorization: Bearer {manager_token}
Content-Type: application/json

{
    "name": "Desserts",
    "description": "Sweet treats and desserts",
    "is_active": true,
    "sort_order": 3
}
```

### Create a dish
```http
POST /manager/dishes
Authorization: Bearer {manager_token}
Content-Type: application/json

{
    "name": "Chocolate Cake",
    "description": "Rich chocolate cake with vanilla ice cream",
    "price": 12.99,
    "category_id": 3,
    "is_vegetarian": true,
    "is_vegan": false,
    "is_gluten_free": false,
    "is_available": true,
    "preparation_time": 15
}
```

## 5. Cashier Endpoints (Requires admin, manager, or cashier token)

### Get orders needing attention
```http
GET /cashier/orders/needing-attention
Authorization: Bearer {cashier_token}
```

### Update order status
```http
PUT /cashier/orders/{order_id}/status
Authorization: Bearer {cashier_token}
Content-Type: application/json

{
    "status": "ready"
}
```

## 6. Customer Endpoints (Requires customer token)

### Get available tables
```http
GET /customer/tables/available?date=2025-08-19&guests=4
Authorization: Bearer {customer_token}
```

### Create a reservation
```http
POST /customer/reservations
Authorization: Bearer {customer_token}
Content-Type: application/json

{
    "table_id": 1,
    "reservation_date": "2025-08-19 19:00:00",
    "guests": 4,
    "special_requests": "Window seat preferred"
}
```

### Create an order
```http
POST /customer/orders
Authorization: Bearer {customer_token}
Content-Type: application/json

{
    "type": "dine_in",
    "table_id": 1,
    "items": [
        {
            "dish_id": 1,
            "quantity": 2,
            "special_instructions": "Medium rare"
        },
        {
            "dish_id": 2,
            "quantity": 1,
            "special_instructions": "Extra crispy"
        }
    ],
    "notes": "Please bring extra napkins"
}
```

## Testing with cURL

### Example: Register a new user
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone_number": "+1234567890",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }'
```

### Example: Login and get token
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@restaurant.com",
    "password": "admin123"
  }'
```

### Example: Get users (with token)
```bash
curl -X GET http://localhost:8000/api/admin/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Default Users for Testing

After running the seeders, you can use these accounts:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@restaurant.com | admin123 |
| Manager | manager@restaurant.com | manager123 |
| Cashier | cashier@restaurant.com | cashier123 |
| Customer | customer@restaurant.com | customer123 |

## Testing Workflow

1. **Start the server**: `php artisan serve`
2. **Test public endpoints** (no authentication required)
3. **Register a new customer** or **login with existing user**
4. **Use the returned token** in the Authorization header for protected endpoints
5. **Test role-based access** by trying different endpoints with different user roles

## Common HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (missing or invalid token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Tips for Testing

1. **Always include the Accept header** with `application/json` for API responses
2. **Use the Bearer token** in the Authorization header for protected routes
3. **Check response status codes** to understand what happened
4. **Validate response data** matches your expectations
5. **Test error cases** by sending invalid data
6. **Test role-based access** by using different user accounts
