# MotoX Workshop Management System

MotoX is a Laravel-based workshop management app for inventory, customers, job orders, billing, reports, settings, notifications, and history logs.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js 20 or newer
- NPM
- SQLite enabled for PHP, or MySQL if you choose to reconfigure `.env`

## First-Time Setup

Run these commands after cloning the repository:

```bash
composer install
composer run setup
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

The setup script copies `.env.example`, creates `database/database.sqlite`, generates the app key, creates the storage link, runs migrations, installs Node dependencies, and builds frontend assets.

## Manual Setup

Use this path if you do not want to run the setup script:

```bash
composer install
copy .env.example .env
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan storage:link
php artisan migrate
npm install
npm run build
php artisan serve
```

On macOS/Linux, replace `copy .env.example .env` with:

```bash
cp .env.example .env
```

## Database

The default `.env.example` uses SQLite so classmates can run the project without creating a MySQL database.

To use MySQL instead, edit `.env`:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motox
DB_USERNAME=root
DB_PASSWORD=
```

Then run:

```bash
php artisan migrate
```

## Uploaded Images

The app stores uploaded profile and inventory images on the public disk. Run this once:

```bash
php artisan storage:link
```

## Development Commands

```bash
npm run dev
php artisan serve
```

Production-style frontend build:

```bash
npm run build
```

Test command:

```bash
php artisan test
```

## Troubleshooting

If uploaded images do not appear, run:

```bash
php artisan storage:link
```

If the SQLite database file is missing, run:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

If frontend styles or scripts look outdated, run:

```bash
npm install
npm run build
```
