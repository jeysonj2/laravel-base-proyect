# Initial Context for GitHub Copilot Agent Mode

We will continue with the project in Laravel 12. So far, we have achieved the following:

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
- Restriction of all user and role routes so that only users with the 'admin' role can access them.
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
    - **User**: Tests for the user management system and email verification.
  - Test of the root route to verify system status.
  - Structured organization of tests into folders by functionality to facilitate maintenance.
  - Implementation of tests for all main API features.
- Implementation of code coverage reporting to evaluate test effectiveness:
  - Configuration of Docker environment with Xdebug support and coverage mode enabled
  - Configuration of PHPUnit to generate detailed coverage reports in HTML and text formats
  - Setup for running tests with coverage metrics via the `--coverage` flag
  - Directory structure for storing coverage reports in an organized manner

**Important Notes:**

- We are using Docker Compose during development, so everything runs in containers and not directly from the terminal. Any command that needs to be executed must be done inside the containers. Please read the docker-compose.yml file to understand the service names in context.
- We are working with Laravel 12, so all solutions must be adapted to this version. If necessary, consult the official Laravel 12 documentation at <https://laravel.com/docs/12.x>.
- Environment variables are obtained as strings, so explicit casting to other data types is necessary when required.
- We will communicate in Spanish or English; however, all generated code, along with messages and comments, must be in English to comply with international standards.
- When adding a new feature, please add the related tests.
- When modifying a feature, please update the related tests if necessary.
- Every time I write "tested and approved" or "probado y aprobado" you will update the `AI Agent Context.md` file with what has been recently done and the `README.md` file if necessary.

Please confirm if you are ready to receive the next task.
