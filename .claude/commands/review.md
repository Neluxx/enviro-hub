Review a specific class or all uncommitted changes against the project's CLAUDE.md and Laravel best practices.

## Input

The argument `$ARGUMENTS` may be:
- A class name or partial path (e.g. `UserController`, `app/Services/CreateUser.php`) — review that specific file
- Empty — review every PHP file touched in `git diff HEAD`

## Steps

1. **Determine scope**
   - If `$ARGUMENTS` is provided, locate the file using Glob or Grep and read it fully. Do not use `git diff` — review the entire current file.
   - If `$ARGUMENTS` is empty, run `git diff HEAD` to get all uncommitted changes and read the full context of each changed PHP file.
2. Read `CLAUDE.md` to load the project's coding standards.
3. Review the code against the rules below.

## What to check

### Laravel best practices
- **Controllers**: Are they thin? Is validation delegated to Form Requests? Are responses using API Resources?
- **Models**: Is `$fillable` or `$guarded` defined? Are casts declared? Is business logic absent from the model?
- **Eloquent**: Are there N+1 query risks (missing eager loading)? Are raw queries avoided where Eloquent suffices?
- **Services / Actions**: Is business logic properly encapsulated and not living in controllers or models?
- **Validation**: Is validation done in Form Request classes, not controllers?
- **Routing**: Are routes named? Are closures used in route files (not allowed in production)?
- **Security**: Is user input validated? Are policies used for authorization? Are secrets hardcoded anywhere?
- **Jobs**: Do queued jobs define `$tries`, `$backoff`, and `failed()`?
- **Naming**: Do classes, methods, routes, and views follow the naming conventions in CLAUDE.md?
- **Testing**: Are new features accompanied by tests? Are factories used instead of hardcoded data?
- **Config**: Is `env()` called directly in application code instead of via `config()`?

### Code quality
- Methods doing more than one thing
- Unnecessary complexity or premature abstractions
- Missing or incorrect type hints
- Dead code or unused imports

## Output format

Structure your review as follows:

---

### Summary
One or two sentences on the overall quality of the changes.

### Issues
List each problem found. Group by file. For each issue:
- **File**: `path/to/file.php` (line X)
- **Severity**: `critical` | `warning` | `suggestion`
- **Issue**: What the problem is
- **Fix**: A concrete recommendation or corrected code snippet

If no issues are found in a file, skip it.

### Verdict
One of:
- **Approved** — no issues found
- **Approved with suggestions** — only minor/suggestion-level findings
- **Changes requested** — one or more `warning` or `critical` issues must be addressed

---

## Rules

- When reviewing a specific class: review the whole file, not just a diff
- When reviewing uncommitted changes: only flag lines that are part of the diff — do not flag pre-existing code unless it directly interacts with the changed lines
- Be specific: reference file paths and line numbers where possible
- Do not invent problems — if something is acceptable, say nothing about it
- If no class is given and there are no uncommitted changes, say so and stop
