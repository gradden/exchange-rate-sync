## Installation
1. Clone the repository
```
   git clone https://github.com/gradden/exchange-rate-sync.git
```

2. Make a .env file, set up docker and enter into the container's terminal
```
   cd exchange-rate-sync
   cp .env.example .env
   docker compose up -d
   docker exec -it -u application exchange-rate-sync bash
```

3. Composer and Laravel
```
   composer install
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
```
The `UserSeeder` will create a test user. Default credentials are defined in the `.env` file, but you can customize it as well. (`BACKPACK_ADMIN_EMAIL,
BACKPACK_ADMIN_PASSWORD`) 
This will make possible to log in to the dashboard.

### ECB config
The app communicates with the European Central Bank's API server (https://data-api.ecb.europa.eu/). This URL can be modified in the `config/ecb.php` if it changes.

## Exchange Rate Sync and CRON Job
The cron job will update the values in the DB. 
If the job detects a gap between the last exchange rate date and today's date in the database, the job will synchronize it back.
To fire up the cron job, you'll need to run this command:
```
php artisan schedule:work
```

Alternatively, you can fill up the database with historical data with the following command:
```
php artisan exr:load --from=2020-01-01 --to=2023-09-01
```

## Laravel Backpack

Laravel backpack running on the following URL: http://localhost:8080 <br>
<b>Default login credentials:</b> admin@mail.com - Teszt123
