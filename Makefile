# Makefile for Symfony Project
# Default shell: bash

# -------- Configuration --------
SYMFONY = bin/console
DDEVPHP = ddev exec php
COMPOSER = ddev composer
PHPUNIT = vendor/bin/phpunit
PHPSTAN = vendor/bin/phpstan
CSFIXER = vendor/bin/php-cs-fixer

# -------- Default-Target --------
.PHONY: help
help:
	@echo "Development:"
	@echo "  app-setup    — Set up application"
	@echo "  git-update   — Update the git project"
	@echo "  update-dev   — Update dev environment"
	@echo "  full-test    — Run tests and linting"
	@echo ""
	@echo "Composer:"
	@echo "  install      — Install dependencies"
	@echo "  update       — Update dependencies"
	@echo "  selfupdate   — Self-update Composer"
	@echo "  recipes      — Update symfony recipes"
	@echo ""
	@echo "Database:"
	@echo "  db-create    — Create local database"
	@echo "  db-drop      — Drop local database"
	@echo "  db-reset     — Reset local database"
	@echo ""
	@echo "Migrations:"
	@echo "  migrate      — Migrate new migrations"
	@echo "  rollback     — Rollback last migration"
	@echo "  reset        — Reset all migrations"
	@echo "  diff         — Generate migration from diff"
	@echo ""
	@echo "Testing:"
	@echo "  test         — Execute tests"
	@echo "  stan         — Static Analysis (PHPStan)"
	@echo "  cs-fix       — Code Style Fixer"
	@echo ""
	@echo "Cache:"
	@echo "  clear-cache  — Clear all caches"

# -------- Development --------
.PHONY: app-setup
app-setup:
	$(MAKE) selfupdate
	$(MAKE) install
	$(MAKE) db-reset
	$(MAKE) test

.PHONY: git-update
git-update:
	git pull

.PHONY: update-dev
update-dev:
	$(MAKE) git-update
	$(MAKE) selfupdate
	$(MAKE) install
	$(MAKE) migrate
	$(MAKE) test

.PHONY: full-test
full-test:
	$(MAKE) test
	$(MAKE) stan
	$(MAKE) cs-fix

# -------- Composer --------
.PHONY: install
install:
	$(COMPOSER) -V
	$(COMPOSER) install --optimize-autoloader

.PHONY: update
update:
	$(COMPOSER) update

.PHONY: selfupdate
selfupdate:
	$(COMPOSER) selfupdate --2

.PHONY: recipes
recipes:
	$(COMPOSER) recipes:update

# -------- Database --------
.PHONY: db-create
db-create:
	$(DDEVPHP) $(SYMFONY) doctrine:database:create --env=dev --if-not-exists

.PHONY: db-drop
db-drop:
	$(DDEVPHP) $(SYMFONY) doctrine:database:drop --env=dev --if-exists --force

.PHONY: db-reset
db-reset:
	$(MAKE) db-drop
	$(MAKE) db-create
	$(MAKE) migrate

# -------- Migrations --------
.PHONY: migrate
migrate:
	$(DDEVPHP) $(SYMFONY) doctrine:migrations:migrate --env=dev --no-interaction

.PHONY: rollback
rollback:
	$(DDEVPHP) $(SYMFONY) doctrine:migrations:migrate prev --env=dev --no-interaction

.PHONY: reset
reset:
	$(DDEVPHP) $(SYMFONY) doctrine:migrations:migrate 0 --env=dev --no-interaction

.PHONY: diff
diff:
	$(DDEVPHP) $(SYMFONY) doctrine:migration:diff

# -------- Testing --------
.PHONY: test
test:
	$(DDEVPHP) $(SYMFONY) doctrine:schema:drop --env=test --force
	$(DDEVPHP) $(SYMFONY) doctrine:schema:create --env=test --no-interaction
	#$(DDEVPHP) $(SYMFONY) doctrine:fixtures:load --env=test --no-interaction
	$(DDEVPHP) $(PHPUNIT) --colors=always

.PHONY: stan
stan:
	$(DDEVPHP) $(PHPSTAN) analyse src

.PHONY: cs-fix
cs-fix:
	$(DDEVPHP) $(CSFIXER) fix src --using-cache=no

# -------- Cache --------
.PHONY: clear-cache
clear-cache:
	$(DDEVPHP) $(SYMFONY) cache:clear --env=dev
	$(DDEVPHP) $(SYMFONY) cache:warmup --env=dev
