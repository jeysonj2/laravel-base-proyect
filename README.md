# Laravel 12 API Base Project

<p align="center">
<a href="https://laravel.com/docs/12.x"><img src="https://img.shields.io/badge/Laravel-12.x-red" alt="Laravel Version"></a>
<a href="https://www.php.net/releases/8.4/en.php"><img src="https://img.shields.io/badge/PHP-8.4-blue" alt="PHP Version"></a>
<a href="https://jwt.io/"><img src="https://img.shields.io/badge/JWT-Auth-green" alt="JWT Auth"></a>
<a href="https://www.postgresql.org/"><img src="https://img.shields.io/badge/Database-PostgreSQL-blue" alt="PostgreSQL"></a>
<a href="https://www.docker.com/"><img src="https://img.shields.io/badge/Docker-Compose-blue" alt="Docker Compose"></a>
</p>

## About This Project

This is a base Laravel 12 project with a RESTful API implementation that includes authentication, user management, role-based access control, and other essential features for building secure and scalable applications.

## Features

- **JWT Authentication System**
  - Login, token refresh, and logout functionality
  - Secure session management
  
- **User Management**
  - Complete CRUD operations for users
  - Role-based access control
  - User profile management
  
- **Email Verification**
  - Automatic email verification for new accounts
  - Manual email verification endpoint
  
- **Security Features**
  - Account lockout system after multiple failed login attempts
  - Password reset functionality
  - Strong password validation
  
- **Standardized API Responses**
  - Consistent JSON response format
  - Proper error handling and status codes
  
- **Documentation**
  - Complete PHPDoc documentation throughout the codebase

## Prerequisites

- Docker and Docker Compose
- Git

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/jeysonj2/laravel-base-proyect.git
   cd laravel-base-proyect
   ```

2. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

3. Configure your environment variables in the `.env` file.

4. Start the Docker containers:
   ```bash
   docker-compose up -d
   ```

## Docker Environment

This project uses Docker Compose with the following services:

- **app**: PHP 8.4 application server (Laravel)
- **webserver**: Nginx web server
- **db**: PostgreSQL database
- **mailpit**: Mail testing service

### Accessing Services

- API: http://localhost:8000
- MailPit UI: http://localhost:8025

## Environment Variables

Important environment variables to configure:

- `MAX_LOGIN_ATTEMPTS`: Maximum failed login attempts before temporary lockout
- `LOGIN_ATTEMPTS_WINDOW_MINUTES`: Time window for counting login attempts
- `ACCOUNT_LOCKOUT_DURATION_MINUTES`: Duration of temporary account lockout
- `MAX_LOCKOUTS_IN_PERIOD`: Number of temporary lockouts before permanent lock
- `LOCKOUT_PERIOD_HOURS`: Time period to count temporary lockouts
- `PASSWORD_RESET_TOKEN_EXPIRY_MINUTES`: Expiration time for password reset tokens

## API Documentation

### Authentication Endpoints

- `POST /api/login`: User login
- `POST /api/refresh`: Refresh JWT token
- `POST /api/logout`: Logout user (authenticated)
- `POST /api/change-password`: Change password (authenticated)
- `GET /api/profile`: Get user profile (authenticated)
- `PUT /api/profile`: Update user profile (authenticated)

### User Management Endpoints (Admin only)

- `GET /api/users`: List all users
- `POST /api/users`: Create new user
- `GET /api/users/{id}`: Get user details
- `PUT /api/users/{id}`: Update user
- `DELETE /api/users/{id}`: Delete user
- `POST /api/users/{user}/resend-verification`: Resend verification email

### Role Management Endpoints (Admin only)

- `GET /api/roles`: List all roles
- `POST /api/roles`: Create new role
- `GET /api/roles/{id}`: Get role details
- `PUT /api/roles/{id}`: Update role
- `DELETE /api/roles/{id}`: Delete role

### Email Verification Endpoints

- `GET /api/verify-email`: Verify email with code

### Password Management Endpoints

- `POST /api/password/email`: Request password reset email
- `POST /api/password/reset`: Reset password with token

### Account Lockout Management Endpoints (Admin only)

- `GET /api/locked-users`: List locked users
- `POST /api/users/{user}/unlock`: Unlock user account

## Testing

Run the tests with the following command:

```bash
docker exec laravel_app php artisan test
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
