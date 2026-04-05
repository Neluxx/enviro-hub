Generate a Conventional Commit message for the staged changes.

## Steps

1. Run `git diff --cached` to see all staged changes.
2. Run `git branch --show-current` to get the current branch name.
3. Analyse the staged changes and determine:
   - **type**: one of `feat`, `fix`, `refactor`, `chore`, `docs`, `test`, `style`, `perf`, `ci`, `build`, `revert`
   - **scope**: only include if the change is clearly isolated to a single module, component, or layer (e.g. `auth`, `api`, `dashboard`) — leave empty otherwise
   - **subject**: short imperative summary, lowercase, no period, max 72 chars
   - **body**: only include if the *why* or *what* is non-obvious from the subject alone — leave empty otherwise
   - **footer**: if the branch name contains an issue number (e.g. `feature/5-add-user-model`, `fix/42-broken-login`), add `Refs: #<number>` in the footer — otherwise leave empty

## Output format

Print the commit message inside a code block so it can be copied easily:

```
<type>(<scope>): <subject>

<body — omit entire line if empty>

<footer — omit entire line if empty>
```

**Examples:**

Minimal (no scope, no body, no footer):
```
feat: add user registration endpoint
```

With issue reference:
```
feat: add user model

Refs: #5
```

With scope and issue reference:
```
fix(auth): resolve token expiry on refresh

Refs: #42
```

With body when non-obvious:
```
refactor: replace manual query builder with Eloquent scopes

Scoped queries were duplicated across three controllers; centralising
them removes ~80 lines and makes future changes easier to apply.

Refs: #17
```

## Rules

- Never invent a scope — omit it when in doubt
- Never pad the body with obvious restatements of the diff
- The subject must be imperative mood ("add", "fix", "remove" — not "added", "fixes", "removed")
- Do not end the subject with a period
- If there are no staged changes, say so and stop
