# Repository Guidelines for AI Agents

## Build, Lint & Test Commands
- Setup: `composer setup` (first time only)
- Development server: `composer dev` or `php artisan serve`
- Production build: `php artisan optimize` (cache config/routes sebelum deploy)
- Run all tests: `composer test` or `php artisan test`
- Run single test: `php artisan test --filter=TestName`
- Run specific test file: `php artisan test tests/Feature/SpecificTest.php`
- Lint PHP code: `./vendor/bin/pint` (follows PSR-12)

## Code Style & Conventions
- PHP: PSR-12 with 4-space indentation, auto-fix with `./vendor/bin/pint`
- Livewire classes: PascalCase (`Login.php`) with matching kebab-case Blade templates (`login.blade.php`)
- Controller methods: Imperative verbs (`index`, `store`, `update`, `destroy`)
- JavaScript: Vanilla scripts in `public/assets` with camelCase functions/variables
- CSS: Static stylesheets in `public/css` (tidak memakai Tailwind pipeline)
- Imports: Group and sort in order: PHP built-in, external libraries, internal/relative
- Error handling: Use try-catch blocks and Laravel's exception handling features
- Naming: Descriptive variable names, consistent terminology across the codebase

## Testing Guidelines
- Feature tests in `tests/Feature`, Unit tests in `tests/Unit`
- Test names: `test_user_can_authenticate` (describe behavior)
- Use `RefreshDatabase` trait for DB tests
- Focus on testing Livewire components' events and validation

## Git Workflow
- Commit messages: Imperative, 72 chars max, optional scope prefix
- Branch: Create feature branch, push, create PR via `gh pr create`
- PRs: Include before/after screenshots for UI changes, ensure tests pass
