# Production Deployment

This guide provides detailed instructions for deploying the Laravel application in a production environment using Docker.

## Server Requirements

- Docker Engine (version 20.10 or higher)
- Docker Compose (version 2.0 or higher)
- At least 2GB of RAM
- At least 20GB of disk space
- Access to ports 80 and 443 (for HTTP and HTTPS)
- A domain name configured to point to the server's IP address

## Environment Variables for Production

Before deploying, you need to configure the environment variables for production. We've included a `.env.prod` template in the project that you can use as a starting point.

### 1. Copy the Production Environment Template

```bash
cp .env.prod .env
```

### 2. Customize the Production Environment Variables

Open the `.env` file and make the following changes:

- Set `APP_URL` and `APP_DOMAIN` to your actual domain
- Set a strong password for `DB_PASSWORD`
- Update `MAIL_FROM_ADDRESS` with your domain email
- Set `ACME_EMAIL` to your actual email for SSL certificate notifications

Here's what the production environment template contains:

```bash
# Application configuration
APP_NAME="Laravel Base Project"
APP_ENV=production
APP_KEY=  # Will be generated automatically
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_DOMAIN=yourdomain.com

# PostgreSQL Database Configuration
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel_production
DB_USERNAME=db_user
DB_PASSWORD=strong_password_here

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailserver
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# JWT Configuration
JWT_SECRET=  # Will be generated automatically
JWT_TTL=60  # Token lifetime in minutes

# Let's Encrypt SSL Configuration
ACME_EMAIL=youremail@example.com

# Additional security and performance settings are included in the template
```

## Initial Deployment

### 1. Clone the Repository

```bash
git clone https://your-repository/project.git
cd project
```

### 2. Configure Environment Variables

```bash
cp .env.prod .env
nano .env  # Or any editor to modify the variables as indicated above
```

### 3. Build and Start the Containers

```bash
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
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
L5_SWAGGER_CONST_HOST=https://api.yourdomain.com
```

### Accessing API Documentation

After deployment, your API documentation will be available at:

```
https://yourdomain.com/api/documentation
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
