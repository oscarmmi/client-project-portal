# Client Project Portal

A single-project status portal where an owner manages a client's milestones and posts progress updates. Clients sign in to a read-only view of their own project, including milestone progress, the next delivery, and the newest updates.

## Features

- Laravel 13, Inertia, Vue 3, and Tailwind CSS.
- Owner and client roles with role-protected routes.
- One active project with editable milestones (`pending`, `in_progress`, and `done`).
- Timestamped owner updates displayed newest first.
- Client-specific, read-only project view with a milestone progress bar.
- SQLite by default; no Redis or external services required.

## Requirements

- PHP 8.3 or newer with the SQLite extension.
- Composer.
- Node.js and npm.

## Local setup

```sh
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

On macOS or Linux, replace `copy .env.example .env` with `cp .env.example .env`.

Start the Laravel server and Vite in separate terminals:

```sh
php artisan serve
npm run dev
```

Open <http://127.0.0.1:8000>. To reset the local database and restore the demo content, run:

```sh
php artisan migrate:fresh --seed
```

## Demo accounts

Seeding prints the local demo login hints to the console:

| Role | Email | Password | Landing page |
| --- | --- | --- | --- |
| Owner | `owner@example.test` | `password` | Project management |
| Client | `client@example.test` | `password` | Client project status |

These credentials are for local development and screenshots. Set unique passwords before using an environment beyond local development.

## Tests

```sh
php artisan test
npm run build
```

## Project structure

- `app/Models`: users, projects, milestones, and project updates.
- `app/Http/Controllers/Owner`: owner project and milestone management.
- `app/Http/Controllers/Client`: client-scoped project display.
- `resources/js/Pages`: Inertia pages for owner and client experiences.
- `database/seeders/DatabaseSeeder.php`: repeatable wedding-photography demo project.
