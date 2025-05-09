You are a highly skilled Laravel developer tasked with add/edit features in the current project.

Your goal is to provide a detailed plan and code structure for the feature based on the given project description and specific requirements.

1. Development Guidelines:
  
- Use PHP 8.3+ features where appropriate
- Follow Laravel conventions and best practices
- Prefer using helpers over facades when possible
- Focus on creating code that provides excellent developer experience (DX), better autocompletion, type safety, and comprehensive docblocks

2. Coding Standards and Conventions:
  
- File names: Use PascalCase (e.g., MyClassFile.php)
- Class and Enum names: Use PascalCase (e.g., MyClass)
- Method names: Use camelCase (e.g., myMethod)
- Variable and Properties names: Use snake_case (e.g., my_variable)
- Constants and Enum Cases names: Use SCREAMING_SNAKE_CASE (e.g., MY_CONSTANT)

3. Testing and Documentation:
  
- Provide an overview of the testing strategy (e.g., unit tests, feature tests)
- Outline the documentation structure, including README.md, usage examples, and API references

Remember to adhere to the specified coding standards, development guidelines, and Laravel best practices throughout your plan and code samples. Ensure that your response is detailed, well-structured, and provides a clear roadmap for developing the Laravel package based on the given project description and requirements.

We will continue with this project in Laravel 12. So far, we have achieved the following:

- Initialization of the project with Laravel 12.
- Configuration of Docker Compose to run the project.
- Database setup in PostgreSQL.
- Migration and creation of models for the users and roles tables.
- Creation of controllers and CRUD endpoints for users and roles.
- All responses are generated in JSON format, including exceptions.
- Implementation of a custom validator to ensure a field is unique, regardless of case sensitivity.
- Creation of an authentication controller with endpoints for login and refresh token.
- Use of JWT for session management, utilizing the `tymon/jwt-auth` library.
- Protection of user and role routes through session management; only users with a valid JWT token can access them.
- Implementation of middleware to restrict access to routes based on the role of the logged-in user.
- Addition of a "superadmin" role with highest level permissions in the system.
- Implementation of special superadmin privileges:
  - Only superadmins can create, update, or delete other superadmin users
  - Only superadmins can assign the superadmin role to other users
  - Protected hierarchical permission system with superadmin > admin > user
- Restriction of all user and role routes so that only users with the 'admin' or 'superadmin' role can access them.
- Creation of an endpoint to send a verification code email to confirm the user's email validity.
- Creation of an endpoint to verify the user's email, which receives a code to validate the email.
- Configuration to automatically send a verification email when creating or editing a user to confirm the account.
- Configuration of Mailpit in Docker Compose to test email sending.
- Implementation of two endpoints for password reset:
  - An endpoint to request a password reset, which sends an email with a unique token.
  - An endpoint to verify the token and set a new password.
- Configuration to send a confirmation email when the password is successfully changed.
- Addition of an environment variable `PASSWORD_RESET_TOKEN_EXPIRY_MINUTES` to control the token expiration time.
- Validation of new passwords using the `strong_password` rule, requiring at least one uppercase letter, one number, and one special character.
- Implementation of a logout endpoint that invalidates the access token.
- Implementation of an endpoint for the authenticated user to change their own password.
- Creation of two endpoints for managing the profile of the logged-in user:
  - An endpoint to retrieve the authenticated user's data.
  - An endpoint to update the authenticated user's data, with the following restrictions:
    - Password changes are not allowed through this endpoint.
    - Role changes are not allowed.
    - If the email is changed, a verification email is automatically sent to the new email.
- Implementation of a user account lockout system after multiple failed login attempts:
  - Temporary lockout after entering the wrong password more than 3 times within a 5-minute period (configurable via environment variables `MAX_LOGIN_ATTEMPTS` and `LOGIN_ATTEMPTS_WINDOW_MINUTES`).
  - The temporary lockout lasts 1 hour by default (configurable via the `ACCOUNT_LOCKOUT_DURATION_MINUTES` environment variable).
  - If a user is temporarily locked out 2 times within a 24-hour period, they are indefinitely locked out (configurable via environment variables `MAX_LOCKOUTS_IN_PERIOD` and `LOCKOUT_PERIOD_HOURS`).
  - When a user is locked out, they receive an email indicating the lockout duration and the option to contact an administrator.
  - Creation of endpoints for administrators to view the list of locked-out users and unlock them.
- Implementation of a standardized JSON response system for all controllers:
  - Creation of an `ApiResponse` class in `app/Http/Responses` that encapsulates the logic for generating consistent responses.
  - Implementation of an `ApiResponseTrait` trait that provides convenient methods like `successResponse`, `errorResponse`, `unauthorizedResponse`, etc.
  - Integration of the trait into the base controller so that all controllers automatically inherit these methods.
  - Standardization of the response format with fields `code`, `message`, and optional `data` to maintain consistency.
  - Customization of error handling during development, showing debugging information only in the development environment.
- Implementation of complete PHPDoc documentation for all project files:
  - Detailed documentation of all models with their properties, relationships, and methods.
  - Exhaustive documentation of all controllers, including parameters, return types, and functionality descriptions.
  - Documentation of services, middlewares, events, listeners, and mail classes.
  - Documentation of configuration and bootstrap files.
  - Documentation of API, web, and console routes.
  - Documentation of the standardized API response system.
  - All following official PHPDoc standards to ensure compatibility with documentation generation tools and facilitate future development.
- Update of the project's README.md file:
  - Creation of a professional README with detailed project information.
  - Inclusion of informative badges about Laravel version, PHP 8.4, JWT, PostgreSQL, and Docker.
  - Clear documentation of all project features and functionalities.
  - Detailed installation and configuration instructions for a Docker environment.
  - Complete list of API endpoints organized by category and functionality.
  - Documentation of important environment variables for project configuration.
- Creation of a complete suite of automated tests to ensure the quality and proper functioning of the project:
  - Implementation of Feature tests organized into categories to test each main aspect of the system:
    - **Auth**: Tests for authentication, login, refresh token, and logout.
    - **Password**: Tests for the password reset and change system.
    - **Profile**: Tests for the user profile management endpoints.
    - **Role**: Tests for role management and deletion (RoleManagementTest, RoleDeletionTest).
    - **User**: Tests for the user management system, email verification, and superadmin permissions (UserManagementTest, SuperadminPermissionsTest).
  - Test of the root route to verify system status.
  - Structured organization of tests into folders by functionality to facilitate maintenance.
  - Creation of shell scripts (tests.sh and tests-coverage.sh) to simplify running tests and generating coverage reports.
  - Implementation of tests for all main API features.
- Implementation of code coverage reporting to evaluate test effectiveness:
  - Configuration of Docker environment with Xdebug support and coverage mode enabled
  - Current code coverage: Classes 95.24% (20/21), Methods 97.06% (99/102), Lines 97.1% (503/518)
  - Complete test coverage for Console Commands, EventServiceProvider, Events, and ApiResponseTrait
  - Comprehensive test suite with feature tests for authentication, email verification, user management and more
  - Unit tests for models, events, service providers, and utility classes
- Implementation of Swagger/OpenAPI documentation for all API endpoints:
  - Installation and configuration of the L5-Swagger package for Laravel
  - Addition of detailed Swagger annotations to all controllers:
    - AuthController
    - EmailVerificationController
    - PasswordResetController
    - RoleController
    - UserController
    - UserLockoutController
  - Enhancement of model schemas with detailed property descriptions:
    - User model with email validation, lockout features, and role relationship details
    - Role model with unique name validation explanation
  - Documentation of complex flows like email verification process and password reset
  - Detailed description of security features like account lockout mechanism
  - Configuration of Swagger UI to use application URL from environment variables
  - Setup for JWT authentication in Swagger UI with bearer token support
  - Comprehensive documentation of request parameters and response formats
- Implementation of code formatting and linting tools to maintain code quality standards:
  - Configuration of Laravel Pint with detailed rules for PHP code formatting
  - Installation and configuration of PHP_CodeSniffer for code standards validation
  - Creation of composer scripts for easy code linting and formatting:
    - `composer lint`: To check code against standards
    - `composer lint:fix`: To automatically fix fixable issues
    - `composer format`: To format code with Laravel Pint
    - `composer format:test`: To check formatting without making changes
    - `composer check-style`: To run all style checks
    - `composer fix-style`: To fix all style issues automatically
  - Integration with IDEs/editors through configuration files:
    - Visual Studio Code settings in `.vscode/settings.json`
    - PhpStorm configuration in `.idea/php.xml`
  - Implementation of GitHub Actions workflow for automated code quality checks
  - Setup of pre-commit hooks configuration with Husky and lint-staged
  - Standardization of code to follow PSR-12 coding standards
  - Configuration of `.editorconfig` for consistent formatting across editors
  - Use of `.gitattributes` for proper line-ending normalization
- Implementation of a production deployment setup with Docker:
  - Creation of a `docker-compose.prod.yml` file optimized for production environments
  - Development of production-specific Dockerfiles with performance and security optimizations:
    - Lightweight PHP-FPM Alpine image with OPcache enabled
    - Optimized Nginx configuration with HTTP/2 and SSL/TLS support
  - Configuration of Let's Encrypt integration for automatic SSL certificate management
  - Implementation of a SMTP server for email delivery in production
  - Creation of production entry point scripts with automatic migrations and optimizations
  - Automatic Swagger documentation generation for production environments
  - Creation of a `.env.prod-example` template with production-ready settings
  - Complete documentation of the deployment process in a dedicated `DEPLOYMENT.md` file
  - Implementation of security best practices for production environments
  - Configuration for creating a default superadmin user during deployment using the environment variables `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD`
- Implementation of protection to prevent users from deleting their own accounts:
  - Modification of the `destroy` method in the `UserController` to check if the authenticated user is trying to delete their own account
  - Addition of a clear error message explaining that users cannot delete their own accounts
  - Updated Swagger API documentation to include this restriction in the 403 Forbidden response section
  - Creation of comprehensive tests to verify this protection works for both admin and superadmin users
  - Implementation of proper type conversion to ensure ID comparison works correctly regardless of type (string or integer)
- Creation of a shell script to run specific tests:
  - Implementation of a flexible command to execute specific test classes
  - Support for running multiple test classes at once, separated by spaces
  - Simple syntax like `./shell-scripts/test.sh Unit\\Models\\RoleTest Feature\\Auth\\LoginTest`
  - Proper handling of backslash escaping in test class names
  - Integration with Laravel's test filtering capabilities using the `--filter` option
- Enhanced email verification system:
  - Added a web interface for email verification that shows a success or error page
  - Created an environment variable `EMAIL_VERIFICATION_URL` to specify a custom URL for email verification
  - Created a configuration file `verification.php` to manage email verification settings
  - Modified the email template to use the configured URL or fall back to the default web interface
  - Created responsive and user-friendly verification success and error pages
  - Added a new `verifyWeb` method to the `EmailVerificationController` to handle web-based verification
  - Added comprehensive tests for the web verification interface
  - Updated Swagger documentation for the new web-based verification endpoint
  - Maintained backward compatibility with the API-based verification endpoint
- Implementation of configurable HTTP port for web server:
  - Added the `HTTP_PORT` environment variable to allow customizing the HTTP port for both development and production environments
  - Updated development configuration files (`.env.example`, `.env.dev-example`, `.env.testing`) to use the variable with a default of port 8000
  - Updated production configuration file (`.env.prod-example`) to use the variable with a default of port 80
  - Modified `docker-compose.yml` to use the `HTTP_PORT` environment variable for port mapping
  - Modified `docker-compose.prod.yml` to use the `HTTP_PORT` environment variable for port mapping
  - Updated Nginx configuration files to use the HTTP_PORT variable in listen directives
  - Enhanced the Nginx start script to properly handle variable substitution for the HTTP port
  - Used `sed` for reliable variable replacement instead of `envsubst` to avoid configuration errors
  - Added debugging output to verify the correct port configuration during container startup
  - Added clear documentation about configuring the HTTP port in the deployment guide
- Implementation of HTTP-only mode for production deployment:
  - Added the `HTTP_ONLY` environment variable to control whether the application runs without HTTPS
  - When `HTTP_ONLY=yes`, the application runs only on HTTP port and doesn't use port 443
  - Updated Nginx start script (`docker/nginx/start-nginx.sh`) to check for the HTTP_ONLY variable and skip HTTPS configuration
  - Improved HTTP_ONLY mode implementation with Docker Compose:
    - Modified `docker-compose.prod.yml` to be HTTP-only by default (no port 443, no certbot service)
    - Created `docker-compose.prod.with-https.yml` that extends the base configuration to add HTTPS support
    - Updated all production shell scripts to dynamically use the appropriate configuration based on HTTP_ONLY value
    - Removed redundant HTTP-only specific files for a more maintainable solution
  - Updated the deployment documentation with detailed instructions for HTTP-only deployment
  - This feature allows deployment in environments where port 443 is already in use by other services
- Implementation of pagination for user lists:
  - Added pagination support to the UserController index method to efficiently handle large datasets
  - Added pagination support to the UserLockoutController index method for better management of locked user accounts
  - Implemented configurable page size through the `per_page` query parameter (defaults to 15)
  - Added bounds checking to ensure `per_page` is always between 1 and 100 for optimal performance
  - Updated the OpenAPI documentation to fully describe the pagination parameters and response structure
  - Enhanced API responses with comprehensive pagination metadata including:
    - Current page number and total pages
    - Navigation links for first, last, next, and previous pages
    - Item count information (from, to, total)
    - Per page settings
  - Improved front-end usability by providing all necessary pagination information for UI components
  - Ensured backward compatibility with existing API consumers

**Important Notes:**

- We are using Docker Compose during development, so everything runs in containers and not directly from the terminal. Any command that needs to be executed must be done inside the containers. Please read the `docker-compose.yml` file to get familiar with the services names.
- Shell scripts located in the folder `./shell-scripts` can run directly in the terminal, they contain the proper docker compose command for each.
- We are working with Laravel 12, so all solutions must be adapted to this version. If necessary, consult the official Laravel 12 documentation at <https://laravel.com/docs/12.x>.
- Environment variables are obtained as strings, so explicit casting to other data types is necessary when required.
- We will communicate in Spanish or English; however, all generated code, along with messages and comments, must be in English to comply with international standards.
- When adding a new feature, please add the related PHPdoc, Swagger annotations and tests.
- When modifying a feature, please update the related PHPdoc, Swagger annotations and tests if necessary.

Remember every time I write to you (Github Copilot Agent Mode) in your prompt the sentence: `tested and approved` or `probado y aprobado`, you will:

- Read the git staged changes to get additional information about the current changes
- Update the `AI Agent Context.md` file with what has been recently done by you (Github Copilot Agent Mode) and also about whatever you found in the git changes
- Update `README.md` file if necessary
- Update `DEPLOYMENT.md` file if necessary
- Run the command `./shell-scripts/fix-style.sh` to fix lint and format
- Run the command `./shell-scripts/tests-coverage.sh` to check all tests are passing and update de coverage percents in the files `AI Agent Context.md` and `README.md`

Please let me know when you are prepare to start working on a new task
