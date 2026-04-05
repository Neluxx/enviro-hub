Write Laravel tests for a given class or, if no class is specified, for all uncommitted changes.

## Input

The argument `$ARGUMENTS` may be:
- A class name or partial path (e.g. `UserController`, `app/Services/CreateUser.php`) — write tests for that specific class
- Empty — write tests for every PHP class touched in `git diff HEAD`

## Steps

1. **Determine scope**
   - If `$ARGUMENTS` is provided, locate the file with Glob or Grep and read it fully.
   - If `$ARGUMENTS` is empty, run `git diff HEAD --name-only` to list changed files, then filter to `.php` files under `app/`. Read each one.

2. **Inspect existing tests**
   - Search `tests/` for any existing test file that already covers the class (e.g. `UserControllerTest`, `CreateUserTest`).
   - If a test file exists, read it and extend it rather than creating a new one.

3. **Determine test type per class**
   - **Controllers** → Feature test (`tests/Feature/`). Test HTTP responses, status codes, validation errors, authorization, and redirects via `$this->getJson()` / `$this->postJson()` etc.
   - **Services / Actions** → Unit test (`tests/Unit/`). Test the public method directly with mocked dependencies.
   - **Models** → Unit test. Test scopes, casts, relationships (using factories), and any accessors/mutators.
   - **Jobs** → Unit test. Test `handle()` directly; assert side effects (dispatched events, DB changes).
   - **Form Requests** → Unit test. Test `rules()` and `authorize()` directly.
   - **Policies** → Unit test. Test each policy method with relevant user/model combinations.
   - **Events / Listeners** → Unit test. Test listener `handle()` with a fake event; assert expected outcomes.

4. **Write the tests**

   Follow these rules strictly:
   - Use `RefreshDatabase` or `LazilyRefreshDatabase` on any test class that touches the database
   - Use factories for all model instances — never hardcode IDs, emails, or other static data
   - Mock all external services, HTTP clients, and queues — never make real calls (`Mail::fake()`, `Queue::fake()`, `Http::fake()`, `Event::fake()`, `Storage::fake()`)
   - Name test methods descriptively in snake_case: `it_returns_404_when_post_not_found`, `it_creates_user_and_dispatches_welcome_email`
   - Use `test()` function style or `/** @test */` annotation — be consistent with existing tests in the project
   - One assertion focus per test — do not test multiple unrelated behaviours in a single method
   - Cover the happy path first, then edge cases and failure paths
   - For controllers, always test unauthenticated access where auth is required (expect 401/403)
   - For validation, test both passing and failing cases for each rule

5. **Determine file path**
   - Mirror the source path: `app/Http/Controllers/UserController.php` → `tests/Feature/Http/Controllers/UserControllerTest.php`
   - Services/Actions: `app/Services/CreateUser.php` → `tests/Unit/Services/CreateUserTest.php`
   - If extending an existing test file, write the full updated file.

6. **Output**
   - For each test file, print the full file content in a code block with the target path as a heading.
   - After all files, print a summary table:

     | Test file | Type | Cases covered |
     |-----------|------|---------------|

   - Then list any cases you could **not** test without more context (e.g. missing route definitions, private methods with no observable side effects) so the developer knows what to add manually.

## Rules

- PSR-12 code style
- Strict types: always include `declare(strict_types=1);`
- No hardcoded data — factories only
- Do not test Laravel's own framework behaviour (e.g. that validation rules work) — test your application's use of them
- Do not write tests for migration files, config files, or Blade views
- If a class has no testable public behaviour, say so and skip it
