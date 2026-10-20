# Folio Maker

A Laravel portfolio workspace with private, per-user collections and project image uploads.

## Requirements

- PHP 8.2 or newer with PDO PostgreSQL (Supabase-compatible), Fileinfo, and OpenSSL enabled
- Composer

## Run locally

```powershell
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

Open `http://127.0.0.1:8000`. The default configuration uses Supabase Postgres for the app database, database-backed sessions, and private local file storage. Password reset emails are written to `storage/logs/laravel.log`.

## Features

- Session-based registration, login, logout, remember-me, and password reset
- Private portfolio CRUD with search, sorting, categories, and tags
- One live portfolio editor for create and edit, with database-backed profile and appearance settings
- Multiple projects and images per portfolio
- Photos, backgrounds, and project images stored on the private local disk with authenticated, owner-checked media delivery
- Local draft recovery and browser-based light/dark preference
- Responsive Blade UI with upload previews and delete confirmation

Open `/portfolios/create` to make a portfolio or `/portfolios/{id}/edit` to update one. Both URLs use the same editor. The older `/portfolio-generator` URL redirects to the new portfolio editor. Use Supabase connection variables in your environment to configure the database.

Run the test suite with `php artisan test`.
