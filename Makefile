.PHONY: help dev up down build test lint fresh logs

# Default target
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ── Docker ───────────────────────────────────────────────────────
dev: ## Start all services in development mode
	docker compose up -d
	@echo "\n  Backend:  http://localhost:8000"
	@echo "  Frontend: http://localhost:5173"
	@echo "  Horizon:  http://localhost:8000/horizon\n"

up: ## Start all services
	docker compose up -d

down: ## Stop all services
	docker compose down

build: ## Rebuild Docker images
	docker compose build --no-cache

logs: ## Tail logs from all services
	docker compose logs -f

logs-app: ## Tail logs from app service
	docker compose logs -f app

# ── Backend ──────────────────────────────────────────────────────
backend-install: ## Install backend dependencies
	cd backend && composer install

backend-test: ## Run backend tests with Pest
	cd backend && vendor/bin/pest --parallel

backend-lint: ## Run Pint code style check
	cd backend && vendor/bin/pint --test

backend-fix: ## Fix code style with Pint
	cd backend && vendor/bin/pint

backend-analyse: ## Run PHPStan static analysis
	cd backend && vendor/bin/phpstan analyse

backend-migrate: ## Run database migrations
	cd backend && php artisan migrate

backend-seed: ## Seed the database
	cd backend && php artisan db:seed

backend-fresh: ## Fresh migrate and seed
	cd backend && php artisan migrate:fresh --seed

# ── Frontend ─────────────────────────────────────────────────────
frontend-install: ## Install frontend dependencies
	cd frontend && npm install

frontend-dev: ## Start frontend dev server
	cd frontend && npm run dev

frontend-test: ## Run frontend tests
	cd frontend && npx vitest run

frontend-test-watch: ## Run frontend tests in watch mode
	cd frontend && npx vitest

frontend-build: ## Build frontend for production
	cd frontend && npm run build

frontend-typecheck: ## Run TypeScript type checking
	cd frontend && npx tsc --noEmit

# ── Combined ─────────────────────────────────────────────────────
install: backend-install frontend-install ## Install all dependencies

test: backend-test frontend-test ## Run all tests

lint: backend-lint frontend-typecheck ## Run all linters

fresh: backend-fresh ## Fresh database with seeds

# ── Setup ────────────────────────────────────────────────────────
setup: ## First-time project setup
	cp backend/.env.example backend/.env
	cd backend && composer install
	cd backend && php artisan key:generate
	cd frontend && npm install
	@echo "\nSetup complete! Run 'make dev' to start."
