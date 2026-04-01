DOCKER_COMPOSE = docker compose
APP   = $(DOCKER_COMPOSE) exec app
FRONT = $(DOCKER_COMPOSE) exec front
DB    = $(DOCKER_COMPOSE) exec db

.DEFAULT_GOAL := help

# ──────────────────────────────────────────────
#  HELP
# ──────────────────────────────────────────────

.PHONY: help
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-28s\033[0m %s\n", $$1, $$2}'

# ──────────────────────────────────────────────
#  PROJECT
# ──────────────────────────────────────────────

.PHONY: up
up: ## Start all containers (detached)
	$(DOCKER_COMPOSE) up -d --build

.PHONY: down
down: ## Stop and remove containers
	$(DOCKER_COMPOSE) down

.PHONY: restart
restart: down up ## Restart all containers

.PHONY: stop
stop: ## Stop containers without removing them
	$(DOCKER_COMPOSE) stop

.PHONY: ps
ps: ## Show running containers
	$(DOCKER_COMPOSE) ps

.PHONY: logs
logs: ## Follow logs for all services
	$(DOCKER_COMPOSE) logs -f

.PHONY: logs-app
logs-app: ## Follow logs for the app (backend) service
	$(DOCKER_COMPOSE) logs -f app

.PHONY: logs-worker
logs-worker: ## Follow logs for the Messenger worker
	$(DOCKER_COMPOSE) logs -f worker

.PHONY: logs-front
logs-front: ## Follow logs for the frontend service
	$(DOCKER_COMPOSE) logs -f front

# ──────────────────────────────────────────────
#  SHELL ACCESS
# ──────────────────────────────────────────────

.PHONY: shell
shell: ## Open a shell in the app (backend) container
	$(APP) sh

.PHONY: shell-front
shell-front: ## Open a shell in the frontend container
	$(FRONT) sh

.PHONY: shell-db
shell-db: ## Open a psql session in the database container
	$(DB) psql -U ziggy -d ziggy

# ──────────────────────────────────────────────
#  BACKEND
# ──────────────────────────────────────────────

.PHONY: composer-install
composer-install: ## Install Composer dependencies
	$(APP) composer install

.PHONY: composer-update
composer-update: ## Update Composer dependencies
	$(APP) composer update

.PHONY: cc
cc: ## Clear the Symfony cache
	$(APP) php bin/console cache:clear

.PHONY: routes
routes: ## List all registered routes
	$(APP) php bin/console debug:router

.PHONY: services
services: ## List all registered services
	$(APP) php bin/console debug:container

# ──────────────────────────────────────────────
#  DATABASE
# ──────────────────────────────────────────────

.PHONY: migrate
migrate: ## Run pending database migrations
	$(APP) php bin/console doctrine:migrations:migrate --no-interaction

.PHONY: migration
migration: ## Generate a new migration from entity diff
	$(APP) php bin/console doctrine:migrations:diff

.PHONY: migration-status
migration-status: ## Show migration status
	$(APP) php bin/console doctrine:migrations:status

.PHONY: db-reset
db-reset: ## Drop, create and migrate the database (⚠ destroys data)
	$(APP) php bin/console doctrine:database:drop --force --if-exists
	$(APP) php bin/console doctrine:database:create
	$(APP) php bin/console doctrine:migrations:migrate --no-interaction

# ──────────────────────────────────────────────
#  FRONTEND
# ──────────────────────────────────────────────

.PHONY: npm-install
npm-install: ## Install npm dependencies
	$(FRONT) npm install

.PHONY: npm-build
npm-build: ## Build the frontend for production
	$(FRONT) npm run build

.PHONY: type-check
type-check: ## Run TypeScript type checking
	$(FRONT) npm run type-check

# ──────────────────────────────────────────────
#  QUALITY
# ──────────────────────────────────────────────

.PHONY: phpstan
phpstan: ## Run PHPStan static analysis
	$(APP) vendor/bin/phpstan analyse

.PHONY: phpcs
phpcs: ## Run PHP_CodeSniffer (style check)
	$(APP) vendor/bin/phpcs

.PHONY: phpcbf
phpcbf: ## Auto-fix code style with PHP_CodeSniffer
	$(APP) vendor/bin/phpcbf

.PHONY: deptrac
deptrac: ## Run Deptrac architecture boundary check
	$(APP) vendor/bin/deptrac analyse

.PHONY: qa
qa: phpcbf phpcs phpstan deptrac ## Run all quality checks (auto-fix first)

# ──────────────────────────────────────────────
#  TESTS
# ──────────────────────────────────────────────

.PHONY: test
test: ## Run the full test suite
	$(APP) php bin/phpunit

.PHONY: test-filter
test-filter: ## Run tests matching a filter  (usage: make test-filter f=MyTest)
	$(APP) php bin/phpunit --filter=$(f)
