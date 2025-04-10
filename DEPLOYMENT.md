# Production Deployment

This guide provides detailed instructions for deploying the Laravel application in a production environment using Docker.

## Server Requirements

- Docker Engine (version 20.10 or higher)
- Docker Compose (version 2.0 or higher)
- At least 2GB of RAM
- At least 20GB of disk space
- Access to ports 80 and 443 (for HTTP and HTTPS)
- A domain name configured to point to the server's IP address

## Initial Deployment

### 1. Clone the Repository

```bash
git clone https://your-repository/project.git
cd project
```

### 2. Configure Environment Variables for Production

```bash
# Copy the Production Environment Template
cp .env.prod-example .env
# Open the new .env file to make some changes
nano .env  # Or any editor to modify the variables as indicated above
```

Make the following changes:

- Set `APP_URL` and `APP_DOMAIN` to your actual domain
- Set a strong password for `DB_PASSWORD`
- Update `MAIL_FROM_ADDRESS` with your domain email
- Set `ACME_EMAIL` to your actual email for SSL certificate notifications
- Configure the superadmin user by setting `SUPER_ADMIN_EMAIL` and optionally `SUPER_ADMIN_PASSWORD`

Here's what the production environment template contains:

```bash
# Application configuration
APP_NAME="Laravel Base Project"
APP_ENV=production
APP_KEY=  # Will be generated automatically
APP_DEBUG=false
APP_URL=https://my-laravel-base-project.test
APP_DOMAIN=my-laravel-base-project.test

# PostgreSQL Database Configuration
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel_production
DB_USERNAME=laravel_production
DB_PASSWORD=strong_password_here

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailserver
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@my-laravel-base-project.test"
MAIL_FROM_NAME="${APP_NAME}"

# JWT Configuration
JWT_SECRET=  # Will be generated automatically
JWT_TTL=60  # Token lifetime in minutes

# Default Superadmin User Configuration
SUPER_ADMIN_EMAIL=superadmin_laravel_base_project@mailinator.com
# Leave SUPER_ADMIN_PASSWORD empty to auto-generate a secure password during deployment
SUPER_ADMIN_PASSWORD=

# Let's Encrypt SSL Configuration
ACME_EMAIL=youremail@example.com

# Additional security and performance settings are included in the template
```

> **IMPORTANT:**
>
> - Leave the `APP_KEY` and `JWT_SECRET` fields empty as they will be automatically generated during the container startup process and saved to your local `.env` file.
> - If you leave `SUPER_ADMIN_PASSWORD` empty, a secure random password will be generated during deployment and displayed in the container logs.

### 3. Build and Start the Containers

```bash
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
```

The startup process:

1. Mounts your local `.env` file into the container
2. Automatically generates secure `APP_KEY` and `JWT_SECRET` values
3. Creates a default superadmin user with the specified email (or <superadmin@example.com> if not specified)
4. Stores these values back to your local `.env` file for persistence
5. Caches configuration for optimal performance

> **Note:** The first time you start the containers, check the logs with `docker compose -f docker-compose.prod.yml logs app` to ensure the keys were generated successfully and to retrieve the auto-generated superadmin password if you didn't specify one.

```bash
# To check the initial setup logs and get the generated superadmin password
docker compose -f docker-compose.prod.yml logs app
```

Look for log entries similar to:

```
Created new superadmin user with email: superadmin@example.com
Generated password: Abx71!kTs9pQ
Please change this password after first login!
```

### 4. Generate SSL Certificates with Let's Encrypt

The certbot container is initially configured in `--staging` mode for testing. Once you've verified that it works correctly, you can switch to production certificates:

```bash
# First, stop the certbot service
docker compose -f docker-compose.prod.yml stop certbot

# Modify the command in docker-compose.prod.yml to remove the --staging flag
# Then, restart the service
docker compose -f docker-compose.prod.yml up -d certbot
```

Note: Certbot will automatically renew certificates before they expire.

## SSL Certificates and HTTP Fallback

### Automatic HTTPS/HTTP Configuration

The system is designed to automatically detect whether SSL certificates are available and configure the web server accordingly:

1. If valid SSL certificates exist in the `/etc/letsencrypt` directory, the web server will serve the application over both HTTP and HTTPS, with HTTP traffic redirected to HTTPS.

2. If SSL certificates are not available (for example, during initial deployment or in development environments), the web server will automatically fall back to serving the application over HTTP only.

This behavior is controlled by a custom script (`docker/nginx/start-nginx.sh`) that checks for certificate existence during container startup and configures the Nginx server appropriately.

### Initial HTTP-Only Mode

When you first deploy the application, it will initially run in HTTP-only mode until valid SSL certificates are generated. This is normal and allows you to:

1. Test the application immediately without waiting for SSL certificates
2. Ensure that the Let's Encrypt verification process (which requires HTTP access) can successfully validate your domain

### Switching Between HTTP and HTTPS

The switch from HTTP-only to HTTPS happens automatically once valid certificates are generated by the certbot container. No manual intervention is required.

If you need to check the current serving mode, you can view the webserver logs:

```bash
docker compose -f docker-compose.prod.yml logs webserver
```

You should see a message indicating whether the service is running with HTTPS+HTTP or HTTP only.

### For Local Development

In local development environments, where SSL certificates are typically not available, the system will automatically operate in HTTP-only mode, making it easier to test your application without dealing with SSL certificate issues.

## External Email Services

If you prefer to use an external email service instead of the built-in SMTP server, modify the following variables in your `.env` file:

### For SendGrid

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_verified_email
MAIL_FROM_NAME="${APP_NAME}"
```

### For Mailgun

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mailgun_username
MAIL_PASSWORD=your_mailgun_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_verified_email
MAIL_FROM_NAME="${APP_NAME}"
```

### For Postmark

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.postmarkapp.com
MAIL_PORT=587
MAIL_USERNAME=your_server_id
MAIL_PASSWORD=your_server_token
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_verified_email
MAIL_FROM_NAME="${APP_NAME}"
```

After changing the mail configuration, restart the containers:

```bash
docker compose -f docker-compose.prod.yml restart app
```

## Application Updates

To update the application to a new version:

```bash
# Get changes from the repository
git pull

# Rebuild and restart the containers
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
```

## Running Migrations Manually

If you need to run specific migrations:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

## Viewing Logs

To view application logs:

```bash
# Laravel application logs
docker compose -f docker-compose.prod.yml logs app

# Nginx web server logs
docker compose -f docker-compose.prod.yml logs webserver

# Database logs
docker compose -f docker-compose.prod.yml logs db
```

## Database Backup

Although you mentioned that backups are made to the entire server, here's a command for making manual database backups if needed:

```bash
docker compose -f docker-compose.prod.yml exec db pg_dump -U ${DB_USERNAME} ${DB_DATABASE} > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Common Issues and Solutions

### Application Key or JWT Secret Issues

If you experience issues with encryption or JWT tokens not working:

1. Check that the `.env` file is correctly mounted as a volume in your docker-compose.prod.yml
2. Verify that the APP_KEY and JWT_SECRET were properly generated in your .env file
3. You can manually regenerate these keys with:

   ```
   docker compose -f docker-compose.prod.yml exec app php artisan config:clear
   docker compose -f docker-compose.prod.yml exec app php artisan key:generate
   docker compose -f docker-compose.prod.yml exec app php artisan jwt:secret
   docker compose -f docker-compose.prod.yml exec app php artisan config:cache
   ```

### SSL Certificate Errors

If there are issues with SSL certificates, check:

1. That the domain is correctly configured to point to your server
2. That ports 80 and 443 are open
3. That the APP_DOMAIN and ACME_EMAIL variables are correctly configured

To troubleshoot Let's Encrypt issues:

```bash
docker compose -f docker-compose.prod.yml logs certbot
```

### Permission Problems

If there are file permission issues, run:

```bash
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker compose -f docker-compose.prod.yml exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

## Security Recommendations

1. **Firewall**: Configure a firewall to allow only necessary ports (80, 443)
2. **Regular Updates**: Keep Docker, the operating system, and dependencies updated
3. **Monitoring**: Implement a monitoring system to detect issues
4. **Limit SSH Access**: Use SSH keys instead of passwords and consider changing the default SSH port
5. **DoS Protection**: Consider implementing protection against denial of service attacks
6. **Backups**: Ensure server backups are performed regularly
7. **Logs**: Review logs regularly for suspicious activities

## API Documentation for Production

The project includes Swagger/OpenAPI documentation that is automatically generated during deployment to ensure it uses the correct production URLs.

### Automatic Swagger Documentation Generation

The production deployment setup automatically generates Swagger documentation during the container initialization process. The `entrypoint.prod.sh` script includes this step, which ensures that:

- All API endpoints in the documentation use your production domain (from your `APP_URL` environment variable)
- Documentation is always up-to-date with your current deployment
- No manual intervention is required

This automatic generation happens once during container startup and doesn't affect performance.

### Custom Swagger URL Configuration (Optional)

By default, the documentation uses the `APP_URL` from your `.env` file. If you need to set a different URL specifically for Swagger (for example, if your API is on a subdomain), you can add this to your `.env` file:

```
L5_SWAGGER_CONST_HOST=https://api.my-laravel-base-project.test
```

### Accessing API Documentation

After deployment, your API documentation will be available at:

```
https://my-laravel-base-project.test/api/documentation
```

### Securing API Documentation in Production

By default, the API documentation is publicly accessible. If you want to restrict access in production, you can add authentication middleware in the `config/l5-swagger.php` file:

```php
'middleware' => [
    'api' => ['auth:api'],
    'asset' => [],
    'docs' => ['auth:api'],
    'oauth2_callback' => [],
],
```

Remember to update this configuration based on your authentication requirements.
