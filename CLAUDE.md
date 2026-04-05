# Laravel Best Practices

## Code Style

- Follow PSR-12 coding standards
- Use PHP 8.x features where appropriate (enums, readonly properties, match expressions, named arguments)
- Keep methods short and focused on a single responsibility
- Prefer expressive, readable code over clever one-liners

## Architecture

### Controllers
- Keep controllers thin — delegate business logic to services or actions
- Use resource controllers (`php artisan make:controller --resource`) for CRUD operations
- Use form request classes for validation, never validate in controllers directly
- Return consistent response formats (use API resources for JSON responses)

### Models
- Define `$fillable` or `$guarded` on every model — never leave both empty
- Define casts in `$casts` for booleans, dates, enums, and JSON columns
- Define relationships as methods, not properties
- Scope query logic into local scopes or dedicated query builder classes
- Do not put business logic in models — keep them as data representations

### Services & Actions
- Encapsulate business logic in service classes or single-action classes
- Action classes should do one thing and be named accordingly (e.g. `CreateUser`, `SendWelcomeEmail`)
- Services should be injected via the constructor, not resolved with `app()` or `resolve()`

### Validation
- Always use Form Request classes for validation
- Define authorization logic in the `authorize()` method of form requests
- Use custom rule classes for complex or reusable validation logic

### Eloquent
- Use eager loading to avoid N+1 queries — always check for missing `with()`
- Prefer `firstOrCreate`, `updateOrCreate`, `upsert` over manual find-then-save logic
- Use database transactions for operations that modify multiple records
- Avoid raw queries; use the query builder or Eloquent — only use `DB::raw` when necessary

### Events & Listeners
- Use events and listeners for side effects (emails, notifications, logging) rather than embedding them in controllers or services
- Prefer queued listeners for anything that can be deferred

### Jobs & Queues
- Move all time-consuming operations (emails, file processing, API calls) to queued jobs
- Always define `$tries`, `$backoff`, and `failed()` on jobs
- Use `ShouldBeUnique` for jobs that should not run in parallel

## Routing

- Name all routes
- Group routes with middleware, prefix, and name prefix using `Route::group` or chained methods
- Keep `routes/web.php` and `routes/api.php` clean — extract large route groups into separate files if needed
- Avoid route closures in production — use controller methods so routes can be cached

## Security

- Never trust user input — always validate and sanitize
- Use Laravel's built-in CSRF protection for web routes
- Authorize every action — use Policies for model-level authorization
- Never expose sensitive data in API responses — use API Resources to control output
- Store secrets in `.env`, never hardcode credentials
- Use `Hash::make()` for passwords — never store plain text
- Rate-limit sensitive endpoints with `throttle` middleware

## Database & Migrations

- Every schema change must have a migration
- Never modify existing migrations that have been committed — create new ones
- Add indexes to columns used in `WHERE`, `ORDER BY`, and foreign keys
- Use foreign key constraints in migrations
- Seed only with `DatabaseSeeder` in production; use separate seeders for development data

## Testing

- Write feature tests for all HTTP endpoints
- Write unit tests for complex business logic (services, actions)
- Use factories for all test data — never hardcode IDs or static data
- Use `RefreshDatabase` or `LazilyRefreshDatabase` in tests that touch the database
- Mock external services and APIs in tests — do not make real HTTP calls

## Configuration & Environment

- Access config values via `config()` helper, never `env()` directly in application code
- Keep `config/` files as the single source of truth for application settings
- Group related config values under a single config file

## Naming Conventions

| Item | Convention | Example |
|------|-----------|---------|
| Controllers | PascalCase, singular | `UserController` |
| Models | PascalCase, singular | `BlogPost` |
| Migrations | snake_case, descriptive | `create_blog_posts_table` |
| Jobs | PascalCase, verb phrase | `ProcessPayment` |
| Events | PascalCase, past tense | `UserRegistered` |
| Listeners | PascalCase, imperative | `SendWelcomeEmail` |
| Policies | PascalCase, `Policy` suffix | `PostPolicy` |
| Form Requests | PascalCase, `Request` suffix | `StorePostRequest` |
| Routes | kebab-case | `/blog-posts/{id}` |
| Route names | dot notation | `blog-posts.show` |
| Blade views | kebab-case | `blog-posts/show.blade.php` |

## Artisan & Tooling

- Use `php artisan make:*` commands to scaffold classes — never create them manually
- Run `php artisan optimize:clear` after config/route/view changes in development
- Use `php artisan db:seed --class=DevelopmentSeeder` (or equivalent) for local data
- Use Laravel Pint (`./vendor/bin/pint`) to enforce code style before committing
