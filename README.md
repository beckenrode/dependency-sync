# Dependency Sync for Composer projects

Reports PHP, Composer, and npm versions from any Composer application to a
Dependency Sync API. It includes first-class integrations for Laravel and
WordPress, plus a framework-neutral CLI and PHP API.

The package supports Laravel 10 and newer.

## Install

```bash
composer require beckenrode/dependency-sync
```

Configure the credentials in your environment:

```dotenv
DEPENDENCY_SYNC_TOKEN=your-secret-token
DEPENDENCY_SYNC_ENDPOINT=https://dependencies.example/api/sync
DEPENDENCY_SYNC_TIMEOUT=30
```

## Any Composer project

Run the Composer-installed binary from the project root:

```bash
vendor/bin/dependency-sync
```

Or call the framework-neutral PHP API:

```php
$result = \DependencySync\DependencySync::reporter(__DIR__)->report();
```

## Laravel

Laravel 10 and newer auto-discovers the included service provider. Publish its
configuration and use the Artisan command:

```bash
php artisan vendor:publish --tag=dependency-sync-config
php artisan dependency-sync:report
```

The command sends a complete snapshot in this format:

```json
{
    "php_version": "8.3.24",
    "composer": [{"name": "laravel/framework", "version": "v12.0.0"}],
    "npm": [{"name": "vite", "version": "6.0.0"}]
}
```

Composer packages are read from Composer's installed package metadata. npm
packages are read from `package-lock.json`. When multiple installed npm versions
share a package name, their versions are combined into one comma-separated value.

### Laravel schedule

Enable the package-managed schedule in `.env`:

```dotenv
DEPENDENCY_SYNC_SCHEDULE_ENABLED=true
DEPENDENCY_SYNC_SCHEDULE="0 * * * *"
```

The default cron expression reports hourly. Laravel's scheduler must also be
running on the host, normally with this system cron entry:

```cron
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

Set `DEPENDENCY_SYNC_TIMEOUT` to change the HTTP timeout from its 30-second
default.

## WordPress

Require the package from the WordPress project's root Composer file. When the
Composer autoloader is loaded, the package registers an hourly WP-Cron event and
a WP-CLI command:

```bash
wp dependency-sync report
```

Credentials can be environment variables or constants in `wp-config.php`:

```php
define('DEPENDENCY_SYNC_TOKEN', 'your-secret-token');
define('DEPENDENCY_SYNC_ENDPOINT', 'https://dependencies.example/api/sync');
```

WP-Cron only runs when WordPress receives traffic. Production sites may instead
run the WP-CLI command from the system scheduler.
