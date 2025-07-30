# Makefile for Symfony Project
# Default shell: bash

# -------- Configuration --------
SRC := ./
BUILD_DIR := build
RELEASE_DIR := build/release
APP_NAME := enviro-hub
VERSION := $(shell cat VERSION.txt)
RELEASE_FILE := $(APP_NAME)_$(VERSION).zip

SYMFONY = bin/console
DDEVPHP = ddev exec php
COMPOSER = ddev composer
PHPUNIT = vendor/bin/phpunit
PHPSTAN = vendor/bin/phpstan
CSFIXER = vendor/bin/php-cs-fixer
MAKEFLAGS += --no-print-directory
MAKEFLAGS += --silent

# -------- ANSI Color Codes --------
RESET   := \033[0m\n
BLACK   := \033[1;30m
RED     := \033[1;31m
GREEN   := \033[1;32m
YELLOW  := \033[1;33m
BLUE    := \033[1;34m
MAGENTA := \033[1;35m
CYAN    := \033[1;36m
WHITE   := \033[1;37m

INFO    := $(BLUE)\n[INFO]
WARNING := $(YELLOW)\n[WARNING]
ERROR   := $(RED)\n[ERROR]

# -------- Default-Target --------
.PHONY: help
help:
	@echo "Development:"
	@echo "  app-setup    — Set up application"
	@echo "  git-update   — Update the git project"
	@echo "  update       — Update dev environment"
	@echo "  full-update  — Update with tests"
	@echo "  reset-update — Update with DB reset"
	@echo "  build        — Build release artifact"
	@echo ""
	@echo "Composer:"
	@echo "  cp-install      — Install dependencies"
	@echo "  cp-update       — Update dependencies"
	@echo "  cp-selfupdate   — Self-update Composer"
	@echo "  cp-recipes      — Update symfony recipes"
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
	$(MAKE) cp-selfupdate
	$(MAKE) cp-install
	$(MAKE) db-reset
	$(MAKE) test

.PHONY: git-update
git-update:
	git pull

.PHONY: update
update:
	@echo "$(INFO) Update dev environment $(RESET)"
	$(MAKE) git-update
	$(MAKE) cp-selfupdate
	$(MAKE) cp-install
	$(MAKE) migrate
	$(MAKE) test

.PHONY: full-update
full-update:
	$(MAKE) update
	$(MAKE) stan
	$(MAKE) cs-fix

.PHONY: reset-update
reset-update:
	$(MAKE) update
	$(MAKE) db-reset

.PHONY: build
build:
	@echo "$(INFO) Build release artifact $(RESET)"
	rm -rf $(BUILD_DIR)
	mkdir -p $(RELEASE_DIR)
	rsync -a --exclude=$(BUILD_DIR) --exclude='.git' --exclude='vendor' --exclude='.env.local' $(SRC) $(RELEASE_DIR)/
	cd $(RELEASE_DIR) && APP_ENV=prod composer install --no-dev --optimize-autoloader
	cd $(RELEASE_DIR) && zip -r ../$(RELEASE_FILE) .

# -------- Composer --------
.PHONY: cp-install
cp-install:
	@echo "$(INFO) Install composer dependencies $(RESET)"
	$(COMPOSER) -V
	$(COMPOSER) install --optimize-autoloader

.PHONY: cp-update
cp-update:
	@echo "$(INFO) Update composer dependencies $(RESET)"
	$(COMPOSER) update

.PHONY: cp-selfupdate
cp-selfupdate:
	@echo "$(INFO) Update composer $(RESET)"
	$(COMPOSER) self-update --2

.PHONY: cp-recipes
cp-recipes:
	@echo "$(INFO) Update symfony recipes $(RESET)"
	$(COMPOSER) recipes:update

# -------- Database --------
.PHONY: db-create
db-create:
	@echo "$(INFO) Create database $(RESET)"
	$(DDEVPHP) $(SYMFONY) doctrine:database:create --env=dev --if-not-exists

.PHONY: db-drop
db-drop:
	@echo "$(WARNING) Drop database $(RESET)"
	$(DDEVPHP) $(SYMFONY) doctrine:database:drop --env=dev --if-exists --force

.PHONY: db-reset
db-reset:
	$(MAKE) db-drop
	$(MAKE) db-create
	$(MAKE) migrate

# -------- Migrations --------
.PHONY: migrate
migrate:
	@echo "$(INFO) Execute migrations $(RESET)"
	$(DDEVPHP) $(SYMFONY) doctrine:migrations:migrate --env=dev --no-interaction

.PHONY: rollback
rollback:
	@echo "$(INFO) Rollback migrations $(RESET)"
	$(DDEVPHP) $(SYMFONY) doctrine:migrations:migrate prev --env=dev --no-interaction

.PHONY: reset
reset:
	@echo "$(WARNING) Reset migrations $(RESET)"
	$(DDEVPHP) $(SYMFONY) doctrine:migrations:migrate 0 --env=dev --no-interaction

.PHONY: diff
diff:
	@echo "$(INFO) Create migration from diff $(RESET)"
	$(DDEVPHP) $(SYMFONY) doctrine:migration:diff

# -------- Testing --------
.PHONY: test
test:
	@echo "$(INFO) Run test suite $(RESET)"
	$(DDEVPHP) $(SYMFONY) doctrine:schema:drop --env=test --force
	$(DDEVPHP) $(SYMFONY) doctrine:schema:create --env=test --no-interaction
	#$(DDEVPHP) $(SYMFONY) doctrine:fixtures:load --env=test --no-interaction
	$(DDEVPHP) $(PHPUNIT) --colors=always

.PHONY: stan
stan:
	@echo "$(INFO) Run static code analysis $(RESET)"
	$(DDEVPHP) $(PHPSTAN) analyse

.PHONY: cs-fix
cs-fix:
	@echo "$(INFO) Run code style fixer $(RESET)"
	$(DDEVPHP) $(CSFIXER) fix --using-cache=no

# -------- Cache --------
.PHONY: clear-cache
clear-cache:
	@echo "$(INFO) Clear cache $(RESET)"
	$(DDEVPHP) $(SYMFONY) cache:clear --env=dev
	$(DDEVPHP) $(SYMFONY) cache:warmup --env=dev
