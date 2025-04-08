# Laravel 12 API Base Project

<p align="center">
<a href="https://laravel.com/docs/12.x"><img src="https://img.shields.io/badge/Laravel-12.x-red" alt="Laravel Version"></a>
<a href="https://www.php.net/releases/8.4/en.php"><img src="https://img.shields.io/badge/PHP-8.4-blue" alt="PHP Version"></a>
<a href="https://jwt.io/"><img src="https://img.shields.io/badge/JWT-Auth-green" alt="JWT Auth"></a>
<a href="https://www.postgresql.org/"><img src="https://img.shields.io/badge/Database-PostgreSQL-blue" alt="PostgreSQL"></a>
<a href="https://www.docker.com/"><img src="https://img.shields.io/badge/Docker-Compose-blue" alt="Docker Compose"></a>
<a href="https://swagger.io/"><img src="https://img.shields.io/badge/Swagger-API%20Documentation-green" alt="Swagger API Documentation"></a>
</p>

## About This Project

This is a base Laravel 12 project with a RESTful API implementation that includes authentication, user management, role-based access control, and other essential features for building secure and scalable applications.

## Features

- **JWT Authentication System**
  - Login, token refresh, and logout functionality
  - Secure session management with `tymon/jwt-auth` package
  - Token validation and management
  
- **User Management**
  - Complete CRUD operations for users
  - Role-based access control with middleware protection
  - User profile management with restricted permissions
  
- **Email Verification**
  - Automatic email verification for new accounts and email updates
  - Manual email verification endpoint with verification codes
  - Auto-resend verification emails when creating or updating users
  
- **Security Features**
  - Advanced account lockout system:
    - Temporary lockouts after multiple failed login attempts
    - Permanent lockouts after repeated temporary blocks
    - Email notifications for locked accounts
    - Admin interface to manage and unlock user accounts
  - Password reset workflow with secure tokens
  - Strong password validation with customizable requirements
  - Confirmation emails for password changes
  
- **Standardized API Responses**
  - Consistent JSON response format for all controllers
  - Proper error handling and status codes
  - Standardized structure with `code`, `message`, and `data` fields
  - Development-specific error details in non-production environments
  
- **Data Validation**
  - Comprehensive input validation for all endpoints
  - Custom case-insensitive uniqueness validator for fields like usernames and emails
  - Data sanitization and normalization
  
- **API Documentation**
  - Complete Swagger/OpenAPI documentation for all endpoints
  - Interactive API testing through Swagger UI
  - Detailed request and response examples
  - Schema definitions for all models
  - Comprehensive documentation of complex flows like email verification and password reset
  
- **PHPDoc Documentation**
  - Complete PHPDoc documentation throughout the codebase
  - Detailed documentation of models, relationships, and methods
  - API endpoints documentation with parameters and response formats

- **Testing and Code Quality**
  - Comprehensive test suite with Unit and Feature tests
  - Code coverage reporting using Xdebug
  - HTML and text-based coverage reports
  - Current code coverage: 92.1%

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

5. Run seeds to add default Roles and Users:

  ```bash
  docker-compose exec app php artisan migrate --seed
  ```

## Docker Environment

This project uses Docker Compose with the following services:

- **app**: PHP 8.4 application server (Laravel)
- **webserver**: Nginx web server
- **db**: PostgreSQL database
- **mailpit**: Mail testing service for email development and testing

### Accessing Services

- API: http://localhost:8000
- Swagger UI Documentation: http://localhost:8000/api/documentation
- MailPit UI: http://localhost:8025

## Environment Variables

Important environment variables to configure:

- `MAX_LOGIN_ATTEMPTS`: Maximum failed login attempts before temporary lockout (default: 3)
- `LOGIN_ATTEMPTS_WINDOW_MINUTES`: Time window for counting login attempts (default: 5)
- `ACCOUNT_LOCKOUT_DURATION_MINUTES`: Duration of temporary account lockout (default: 60)
- `MAX_LOCKOUTS_IN_PERIOD`: Number of temporary lockouts before permanent lock (default: 2)
- `LOCKOUT_PERIOD_HOURS`: Time period to count temporary lockouts (default: 24)
- `PASSWORD_RESET_TOKEN_EXPIRY_MINUTES`: Expiration time for password reset tokens
- `L5_SWAGGER_CONST_HOST`: Host for Swagger documentation (defaults to APP_URL)

## API Documentation

Interactive API documentation is available through Swagger UI at `/api/documentation`.

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

## Response Format

All API responses follow a standardized format:

```json
{
  "code": 200,
  "message": "Operation successful",
  "data": {
    // Response data here
  }
}
```

Error responses follow the same format with appropriate status codes and error messages:

```json
{
  "code": 400,
  "message": "Validation failed",
  "errors": {
    // Validation errors or other error details
  }
}
```

## Testing

Run the tests with the following command:

```bash
docker-compose exec app php artisan test
```

To run tests with code coverage reporting:

```bash
docker-compose exec app php artisan test --coverage
```

This generates:

- A detailed HTML report in the `coverage-report` directory
- A text summary in `coverage.txt`

You can view the HTML report by opening `coverage-report/index.html` in your web browser.

## Generating Swagger Documentation

After making changes to the API controllers or models, regenerate the Swagger documentation with:

```bash
docker-compose exec app php artisan l5-swagger:generate
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
