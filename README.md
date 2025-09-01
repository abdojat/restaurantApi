# Restaurant Management System API

A comprehensive Laravel-based REST API for managing restaurant operations including user management, table reservations, menu management, and order processing.

## Features

### User Management
- **Admin**: Full system access and control
- **Manager**: Can manage users, tables, reservations, and menu
- **Cashier**: Can manage orders and update order statuses
- **Customer**: Can make reservations and place orders

### Core Functionality
- Table reservations and management
- Menu management with categories and dishes
- Order processing and tracking
- Role-based access control
- User profiles and authentication

## API Endpoints

### Authentication (Public)
```
POST /api/auth/register          - User registration
POST /api/auth/login             - User login
POST /api/auth/password/forgot   - Forgot password
POST /api/auth/password/reset    - Reset password
```

### Menu (Public)
```
GET  /api/menu                   - Get complete menu
GET  /api/menu/categories        - Get menu categories
GET  /api/menu/categories/{id}/dishes - Get dishes by category
GET  /api/menu/dishes/{id}       - Get dish details
GET  /api/menu/search            - Search dishes
GET  /api/menu/popular           - Get popular dishes
GET  /api/menu/highlights        - Get menu highlights
GET  /api/menu/dietary/{preference} - Get dishes by dietary preference
```

### Protected Routes

#### Auth Management (Authenticated Users)
```
GET  /api/auth/me                - Get user profile
POST /api/auth/logout            - Logout
PUT  /api/auth/profile           - Update profile
PUT  /api/auth/phone             - Update phone number
PUT  /api/auth/password          - Change password
POST /api/auth/email/verification-notification - Send verification email
GET  /api/auth/email/verify      - Verify email
```

#### Admin Routes (Admin Only)
```
GET  /api/admin/users            - Get all users
GET  /api/admin/users/{id}       - Get specific user
POST /api/admin/users            - Create user
PUT  /api/admin/users/{id}       - Update user
DELETE /api/admin/users/{id}     - Delete user

GET  /api/admin/roles            - Get all roles
POST /api/admin/roles            - Create role
PUT  /api/admin/roles/{id}       - Update role
DELETE /api/admin/roles/{id}     - Delete role

GET  /api/admin/stats            - Get system statistics
```

#### Manager Routes (Admin + Manager)
```
# Table Management
GET  /api/manager/tables         - Get all tables
POST /api/manager/tables         - Create table
PUT  /api/manager/tables/{id}    - Update table
DELETE /api/manager/tables/{id}  - Delete table

# Reservation Management
GET  /api/manager/reservations   - Get all reservations
PUT  /api/manager/reservations/{id}/status - Update reservation status

# Category Management
GET  /api/manager/categories     - Get all categories
POST /api/manager/categories     - Create category
PUT  /api/manager/categories/{id} - Update category
DELETE /api/manager/categories/{id} - Delete category

# Dish Management
GET  /api/manager/dishes         - Get all dishes
POST /api/manager/dishes         - Create dish
PUT  /api/manager/dishes/{id}    - Update dish
DELETE /api/manager/dishes/{id}  - Delete dish
```

#### Cashier Routes (Admin + Manager + Cashier)
```
GET  /api/cashier/orders         - Get all orders
GET  /api/cashier/orders/needing-attention - Get orders needing attention
GET  /api/cashier/orders/today-summary - Get today's order summary
GET  /api/cashier/orders/{id}    - Get specific order
PUT  /api/cashier/orders/{id}/status - Update order status
PUT  /api/cashier/orders/{orderId}/items/{itemId}/status - Update order item status
PUT  /api/cashier/orders/{id}/delivered - Mark order as delivered
PUT  /api/cashier/orders/{id}/cancel - Cancel order
GET  /api/cashier/orders/table/{tableId} - Get orders by table
GET  /api/cashier/orders/user/{userId} - Get orders by user
```

#### Customer Routes (Customer Only)
```
# Table Reservations
GET  /api/customer/tables/available - Get available tables
POST /api/customer/reservations   - Create reservation
GET  /api/customer/reservations   - Get user's reservations
PUT  /api/customer/reservations/{id}/cancel - Cancel reservation

# Orders
GET  /api/customer/dishes         - Get available dishes
POST /api/customer/orders         - Create order
GET  /api/customer/orders         - Get user's orders
GET  /api/customer/orders/{id}    - Get specific order
PUT  /api/customer/orders/{id}/cancel - Cancel order
GET  /api/customer/orders/{id}/track - Track order
```

## Database Schema

### Core Tables
- **users** - User accounts with role relationships
- **roles** - User roles (admin, manager, cashier, customer)
- **user_roles** - Many-to-many relationship between users and roles
- **tables** - Restaurant tables with capacity and status
- **categories** - Menu categories
- **dishes** - Menu items with dietary information
- **reservations** - Table reservations
- **orders** - Food orders
- **order_items** - Individual items in orders

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env` and configure database
4. Generate application key: `php artisan key:generate`
5. Run migrations: `php artisan migrate:fresh --seed`
6. Start the server: `php artisan serve`

## Default Users

After seeding, the following users are created:

- **Admin**: admin@restaurant.com / admin123
- **Manager**: manager@restaurant.com / manager123
- **Cashier**: cashier@restaurant.com / cashier123
- **Customer**: customer@restaurant.com / customer123

## Authentication

The API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:

```
Authorization: Bearer {token}
```

## Role-Based Access Control

- **Admin**: Full access to all endpoints
- **Manager**: Can manage tables, reservations, menu, and view orders
- **Cashier**: Can manage orders and update their statuses
- **Customer**: Can make reservations and place orders

## File Uploads

The system supports image uploads for:
- User avatars
- Category images
- Dish images

Images are stored in the `storage/app/public` directory and should be accessible via the `storage:link` command.

## Business Logic

### Table Reservations
- Tables can only be reserved if available for the requested time
- Guest count must not exceed table capacity
- Reservations can be confirmed, cancelled, or completed

### Order Management
- Orders go through statuses: pending → processing → ready → delivered
- Order items can be tracked individually
- Cashiers can update order and item statuses
- Orders can be cancelled if still pending

### Menu Management
- Dishes are organized by categories
- Dietary preferences (vegetarian, vegan, gluten-free) are supported
- Dishes can be marked as available/unavailable
- Images and preparation times are supported

## Error Handling

The API returns appropriate HTTP status codes and error messages:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Testing

Run the test suite with:
```bash
php artisan test
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
