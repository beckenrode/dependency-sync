# Laravel Dependency Sync

Reports a Laravel application's PHP version and installed Composer and npm
package versions to a Dependency Sync API.

The package supports Laravel 10 and newer.

## Install

```bash
composer require beckenrode/dependency-sync
php artisan vendor:publish --tag=dependency-sync-config
```

Add the credentials issued by your dependency sync API:

```dotenv
DEPENDENCY_SYNC_TOKEN=your-secret-token
DEPENDENCY_SYNC_ENDPOINT=https://dependencies.example/api/sync
```

## Run manually

```bash
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

## Schedule

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
