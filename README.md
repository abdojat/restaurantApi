# Shami Restaurant — REST API

A production-ready Laravel (v12+) REST API for managing restaurant operations: user & role management, menu (categories & dishes), table reservations, order processing, image uploads (Cloudinary support), and deployment-ready Docker configuration.

Table of contents
- Overview
- Key features
- Quick start (dev)
- Configuration
- Database & seeders
- API summary
- Authentication & RBAC
- File uploads (Cloudinary)
- Deployment (Docker + Render)
- Testing
- Contributing
- License

## Overview

Shami Restaurant API is designed as a modular, secure, and deployable backend for restaurant mobile/web apps. It follows Laravel best practices and includes:

- Role-based access control (Admin, Manager, Cashier, Customer)
- Reservation handling with capacity and availability checks
- Full order lifecycle management (pending → processing → ready → delivered)
- Menu management with dietary flags (vegetarian, vegan, gluten-free), discounts and ratings
- Image upload support (local storage or Cloudinary)
- Dockerized setup and Render CI/CD blueprint (`render.yaml`)

## Key features

- Authentication with Laravel Sanctum (token-based API auth)
- User management and default seeded accounts for quick testing
- Manager endpoints for tables, categories and dishes
- Cashier endpoints for order processing and tracking
- Public menu endpoints (search, popular, dietary filters)
- Database-agnostic configuration (SQLite for local dev, PostgreSQL for production)
- Production-ready Dockerfile with PHP opcache and optimized build steps

## Quick start (local development)

Prerequisites: PHP 8.2+, Composer, Node (optional), and Docker (optional).

1. Clone the repository:

```bash
git clone https://github.com/abdojat/restaurantApi.git
cd restaurantApi/server
```

2. Install PHP dependencies:

```bash
composer install
```

3. Copy environment file and generate app key:

```bash
cp .env.example .env
php artisan key:generate
```

4. Create sqlite file (local dev) and run migrations & seeders:

```bash
php artisan migrate:fresh --seed
```

5. Start the dev server:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Visit: http://127.0.0.1:8000

Notes:
- For a Docker-based local dev, build and run the included Dockerfile or use the `docker/` helpers.
- To serve uploaded images in dev, run `php artisan storage:link`.

## Configuration

Important files and settings:

- `config/database.php` — default uses `pgsql` but the repo includes SQLite settings for local development.
- `.env` — application environment and database credentials; update when deploying to production.
- `render.yaml` — Render blueprint with PostgreSQL and web service configuration.
- `Dockerfile`, `docker/start.sh`, `docker/nginx.conf`, `docker/supervisord.conf` — production-ready Docker configuration.

Environment variables you should set for production (minimum):

- `APP_ENV`, `APP_DEBUG`, `APP_KEY`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `CLOUDINARY_URL` or `CLOUDINARY_*` variables (optional, see Cloudinary section)

## Database & default seeders

The repository includes migrations and seeders which create:

- Roles and default users (admin, manager, cashier, customer)
- Sample categories, dishes, tables, reservations, and orders

Default seeded credentials (development/testing only):

- Admin: `admin@restaurant.com` / `admin123`
- Manager: `manager@restaurant.com` / `manager123`
- Cashier: `cashier@restaurant.com` / `cashier123`
- Customer: `customer@restaurant.com` / `customer123`

Always change default passwords before deploying to production.

## API summary

Base URL (local dev): `http://127.0.0.1:8000/api`

High-level endpoints (examples):

- Authentication (public):
	- `POST /api/auth/register` — register new user
	- `POST /api/auth/login` — login and retrieve token

- Public menu endpoints:
	- `GET /api/menu` — complete menu
	- `GET /api/menu/categories` — list categories
	- `GET /api/menu/search` — search and filter dishes

- Manager (Admin + Manager):
	- `POST /api/manager/dishes` — create dish (supports image upload)
	- `POST /api/manager/categories` — create category

- Cashier (Admin + Manager + Cashier):
	- `GET /api/cashier/orders` — list orders
	- `PUT /api/cashier/orders/{id}/status` — update order status

- Customer:
	- `POST /api/customer/reservations` — create reservation
	- `POST /api/customer/orders` — create order

For a more complete list of endpoints and example requests, see `API_TESTING_GUIDE.md` in the repository.

## Authentication & RBAC

Authentication: Laravel Sanctum token-based authentication. Include token in header:

```
Authorization: Bearer {token}
```

Role-based access control is enforced across routes:

- Admin — full access
- Manager — manage menu, tables and reservations
- Cashier — manage orders
- Customer — make reservations and create orders

## File uploads & Cloudinary

The application supports image uploads for avatars, categories and dishes. By default images are stored in `storage/app/public` (use `php artisan storage:link` to expose them).

Cloudinary integration (optional):

1. Install SDK (optional):

```bash
composer require cloudinary/cloudinary_php guzzlehttp/guzzle
```

2. Set one of the following in `.env`:

```text
CLOUDINARY_URL=cloudinary://<API_KEY>:<API_SECRET>@<CLOUD_NAME>
# OR
CLOUDINARY_CLOUD_NAME=...
CLOUDINARY_API_KEY=...
CLOUDINARY_API_SECRET=...
CLOUDINARY_UPLOAD_PRESET=...
```

When configured, manager endpoints use Cloudinary for uploads and store the secure URL in `image_path`.

## Deployment (Docker + Render)

This repository contains a production-ready `Dockerfile` and `render.yaml` for deploying to Render.

Recommended production flow:

1. Push repository to GitHub.
2. Update `render.yaml` `repo` value to your repo URL.
3. Create a Render Blueprint or Web Service that uses `./server/Dockerfile` and `./server` as context.
4. Configure environment variables in Render (APP_KEY will be auto-generated if enabled).
5. Verify health check endpoint: `/api/menu/recommendations`.

Post-deploy notes:
- The provided `Dockerfile` enables `pdo_pgsql` for PostgreSQL and configures opcache.
- `render.yaml` includes a PostgreSQL service and sample environment variables.

## Testing

Run unit and feature tests with:

```bash
php artisan test
```

API testing examples and curl snippets are available in `API_TESTING_GUIDE.md`.

## Contributing

We welcome contributions. Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-change`
3. Run tests and add any new tests for your changes
4. Submit a pull request with a clear description and motivation

Coding style:
- Follow PSR-12 for PHP code style
- Keep commits small and focused

## Security & best practices

- Do not commit `.env` or secrets to source control
- Rotate default passwords and API keys before production
- Use HTTPS for all production traffic
- Regularly update Composer and OS packages to patch vulnerabilities

## Files of interest

- `server/Dockerfile` — production Dockerfile
- `server/render.yaml` — Render deployment blueprint
- `API_TESTING_GUIDE.md` — API examples and testing instructions
- `CLOUDINARY_README.md` — Cloudinary integration notes

## License

This project is released under the MIT License. See LICENSE for details.
