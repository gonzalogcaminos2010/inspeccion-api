# CLAUDE.md — api-inspeccion

## Project Overview

**American Advisor Inspecciones** — Laravel 12 API for mining vehicle inspection management. PHP 8.2+, SQLite, Sanctum auth, Tailwind CSS 4, Vite.

Clients (mining companies) request inspections of their fleet vehicles. Admins create work orders, assign inspectors. Inspectors perform structured inspections using configurable templates, recording answers, photos, and findings. The system auto-calculates pass/fail results.

## Documentation Files

- **`project_spec.md`** — Business context, actors, core workflow, and all business rules (flag detection, scoring, template sync, numbering, statuses).
- **`architecture.md`** — Technical architecture: full database schema (14 domain tables), complete endpoint list (52 routes), API resources, response format, seeder data.
- **`project_state.md`** — Current implementation state, what has been verified, default credentials, and what remains to be done.

**Read these files first when resuming work on this project.**

## Current State (as of 2026-03-15)

- All 17 migrations and seeders functional
- 52 API routes registered across 11 controllers
- 14 Eloquent models with relationships
- 14 API Resource classes
- Full inspection workflow: Request -> WorkOrder -> Inspection -> Answers -> Submit (with auto-scoring)
- Code formatted with Laravel Pint
- **No automated tests, no FormRequest classes, no role-based middleware, no PDF reports**

## Default Credentials

| Role      | Email                             | Password |
| --------- | --------------------------------- | -------- |
| Admin     | admin@americanadvisor.com         | password |
| Inspector | inspector@americanadvisor.com     | password |

## Common Commands

```bash
# Development
composer run dev          # Start dev server (Laravel + queue + Vite)
php artisan serve         # Laravel server only

# Testing
composer run test         # Run PestPHP tests
php artisan test          # Alternative test runner

# Database
php artisan migrate       # Run migrations
php artisan migrate:fresh --seed  # Reset and seed DB

# Code Style
./vendor/bin/pint         # Fix code style (Laravel Pint)

# Setup (first time)
composer run setup        # Install deps, generate key, migrate, build frontend

# Routes
php artisan route:list    # List all registered routes
```

## Architecture

- **Framework:** Laravel 12
- **Database:** SQLite (default, configurable)
- **Auth:** Laravel Sanctum (token-based)
- **Testing:** PestPHP
- **Frontend:** Vite + Tailwind CSS 4
- **Code Style:** Laravel Pint (PSR-12 based)
- **Response format:** Standardized JSON via `ApiResponse` trait (success/message/data/pagination)

## Key Directories

- `app/Http/Controllers/Api/V1/` — 11 API controllers
- `app/Http/Resources/` — 14 API Resource classes
- `app/Models/` — 14 Eloquent models
- `app/Traits/ApiResponse.php` — Standardized response trait
- `database/migrations/` — 17 migration files
- `database/seeders/` — DatabaseSeeder + InspectionTemplateSeeder
- `routes/api.php` — All API routes (v1 prefix)

## Conventions

- Follow Laravel conventions (Resource controllers, Form Requests, API Resources)
- Use PestPHP syntax for tests
- Run `./vendor/bin/pint` before committing to ensure consistent style
- All API routes versioned under `/api/v1/`
- All controllers use the `ApiResponse` trait for consistent responses
