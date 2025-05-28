# Sheba Platform

A modern service booking platform that allows users to book various services and administrators to manage services and bookings.

## Features

-   Service listing and search
-   Service categorization
-   Booking management
-   Admin dashboard
-   Authentication system
-   RESTful API

## Prerequisites

-   Docker and Docker Compose
-   Git

## Quick Start with Docker

1. Clone the repository:

```bash
git clone https://github.com/SayefEshan/sheba-platform
cd sheba-platform
```

2. Copy the environment file:

```bash
cp .env.example .env
```

3. Build and start the Docker containers:

```bash
# Build the containers
docker compose build

# Start the containers
docker compose up -d
```

This will start the following services:

-   Nginx (Port 80)
-   PHP-FPM 8.1
-   MySQL 8.0
-   Redis

4. Install PHP dependencies:

```bash
docker compose exec app composer install
```

5. Generate application key:

```bash
docker compose exec app php artisan key:generate
```

6. Run database migrations and seeders:

```bash
docker compose exec app php artisan migrate --seed
```

## Default Admin Credentials

After running the seeders, you can log in with these credentials:

```
Email: admin@sheba.xyz
Password: admin123
```

## Development Workflow

1. Start the development servers:

```bash
# Backend (Laravel)
docker compose exec app php artisan serve

# Frontend (Vite)
docker compose exec node npm run dev
```

2. Access the application:

-   Backend API: http://localhost:8000/api/v1

## Database Management

1. Access MySQL:

```bash
docker compose exec db mysql -u root -p
```

2. Reset database:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Running Tests

1. Run PHP unit tests:

```bash
docker compose exec app php artisan test
```

## API Documentation

The API documentation is available in multiple formats:

1. Postman Collection:

    - File: [Sheba-Platform-API.postman_collection.json](Sheba-Platform-API.postman_collection.json)
    - Import this file into Postman to get started

Key API endpoints:

-   Health Check: `GET /`
-   Services: `GET /services`
-   Bookings: `POST /bookings`
-   Admin: `POST /admin/login`
