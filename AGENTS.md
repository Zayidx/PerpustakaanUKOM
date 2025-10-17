# Repository Guidelines

## Project Structure & Module Organization
This Laravel 12 application keeps domain code in `app/` (controllers under `app/Http`, Livewire components in `app/Livewire`, models in `app/Models`). Database migrations and seeders live in `database/migrations` and `database/seeders`. Blade views, Livewire templates, and Vite entrypoints sit in `resources/views` and `resources/js`. Public assets compile through Vite into `public/`. Browser-ready prototypes that are not part of the build are stored under `test-fronten/`. HTTP and console routes are defined in `routes/web.php` and `routes/console.php`. Runtime artifacts stay inside `storage/`.

## Build, Test, and Development Commands
Run `composer setup` the first time to install PHP packages, copy `.env`, generate the key, run migrations, install Node modules, and produce a production build. During daily development, launch `composer dev` to boot the PHP server, queue listener, log viewer, and Vite in one terminal. For lightweight setups, `php artisan serve` and `npm run dev` can be started separately. Create production assets with `npm run build`. Execute the Laravel test suite with `composer test` or directly via `php artisan test`.

## Coding Style & Naming Conventions
PHP files follow PSR-12 with four-space indentation; auto-fix with `./vendor/bin/pint`. Name Livewire classes in PascalCase (`Login.php`) and match Blade templates using kebab-case (`login.blade.php`). Route names and controller methods should read as imperative verbs (`index`, `store`). JavaScript uses modern ES modules in `resources/js`; stick to camelCase for functions and variables. Tailwind CSS 4 drives styling; keep shared utilities in `resources/css`.

## Testing Guidelines
Tests reside in `tests/Feature` for HTTP flows and `tests/Unit` for isolated logic. Prefer describing behavior in the test method name (`test_user_can_authenticate`). Run focused suites with `php artisan test --filter=LoginTest`. Ensure database tests either refresh migrations (`use RefreshDatabase`) or seed via dedicated seeders. Aim to cover every new Livewire component with at least one feature test that exercises emitted events and validation.

## Commit & Pull Request Guidelines
Git history currently uses short imperative summaries (`Starter`). Maintain that tone, limit the subject to 72 characters, and optionally prefix a scope (e.g., `auth: add login form`). Squash noisy fixups before opening a pull request. PRs should: explain the change, list any migrations or seeding steps, reference related issues, and include before/after screenshots for UI updates. Confirm `composer test` and `npm run build` pass before requesting review.

## Environment & Configuration Tips
Every contributor needs a `.env` based on `.env.example`; update database credentials and queue drivers to match local services. Run `php artisan migrate --seed` whenever schema or seeders change. Avoid committing credentials, the `.env` file, or contents of `storage/` and `node_modules/`. Use `php artisan storage:link` if working with uploaded files locally.
