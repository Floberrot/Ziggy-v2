# Ziggy

> A per-cat calendar app — track daily tasks and events for each of your cats.

Owners place **chips** (task markers) on a per-cat calendar every day. Pet sitters can be invited to collaborate. Multiple user roles, custom chip types, weight history, and a clean REST API back a Vue 3 SPA.

---

## Stack

| Layer | Technology |
|---|---|
| Backend | Symfony 8 · PHP 8.4 · Hexagonal Architecture + DDD |
| Frontend | Vue 3 · TypeScript · TanStack Query · Pinia · Tailwind CSS 4 |
| Server | FrankenPHP 1 (Caddy) |
| Database | PostgreSQL 16 |
| Messaging | Symfony Messenger (async worker) |
| Auth | JWT (LexikJWTAuthenticationBundle) |
| Mail | Mailpit (dev) |
| Quality | PHPStan 10 · PHPCS · Deptrac · vue-tsc |

---

## Project Structure

```
ziggy-v2/
├── back/                   # Symfony backend (Hexagonal + DDD)
│   ├── src/
│   │   ├── Calendar/       # Bounded context: calendars & chips
│   │   ├── Cat/            # Bounded context: cat profiles & weight history
│   │   ├── ChipType/       # Bounded context: chip type catalog
│   │   ├── Identity/       # Bounded context: users, auth, invitations
│   │   └── Shared/         # Cross-cutting: exceptions, middleware
│   └── tests/
│       ├── Functional/     # HTTP tests (WebTestCase + JWT)
│       ├── Integration/    # Handler tests (KernelTestCase)
│       └── Unit/           # Domain model & service tests
├── front/                  # Vue 3 SPA (Atomic Design)
│   └── assets/
│       ├── api/            # TanStack Query fetch functions
│       ├── components/     # atoms / molecules / organisms / templates / pages
│       ├── composables/    # Shared composables (use* prefix)
│       ├── stores/         # Pinia stores (UI state only)
│       └── types/          # TypeScript interfaces
├── docker-compose.yml      # Full stack orchestration
├── Dockerfile              # Multi-stage production build
└── Makefile                # All development commands
```

---

## Getting Started

### Prerequisites

- Docker & Docker Compose

### 1. Clone and start

```bash
git clone https://github.com/floberrot/ziggy-v2.git
cd ziggy-v2
make up
```

This starts five containers:

| Container | Purpose | URL |
|---|---|---|
| `app` | Symfony API (FrankenPHP) | http://localhost:80 |
| `front` | Vite dev server | http://localhost:5173 |
| `worker` | Async message queue | — |
| `db` | PostgreSQL 16 | localhost:5432 |
| `mailpit` | Email catch-all | http://localhost:8025 |

### 2. Install dependencies

```bash
make composer-install
make npm-install
```

### 3. Run migrations

```bash
make migrate
```

### 4. (Optional) Load fixtures

```bash
make fixtures
```

The app is now running at **http://localhost:5173**.

---

## Environment Variables

Copy `.env` and adjust as needed. Key variables:

| Variable | Description |
|---|---|
| `APP_SECRET` | Symfony secret key |
| `DATABASE_URL` | PostgreSQL DSN |
| `JWT_PASSPHRASE` | Passphrase for JWT key pair |
| `MAILER_DSN` | SMTP transport (`null://null` in dev) |
| `MESSENGER_TRANSPORT_DSN` | Async queue DSN (`doctrine://default`) |
| `FRONTEND_BASE_URL` | Vue app origin (for CORS) |

JWT keys are auto-generated on first boot. In production, pass `JWT_PASSPHRASE` as a build arg to the Dockerfile.

---

## Development Commands

```bash
# Stack
make up              # Start all containers
make down            # Stop and remove containers
make restart         # Restart containers
make logs            # Follow all logs
make logs-app        # Follow backend logs
make shell           # Open backend shell
make shell-front     # Open frontend shell

# Database
make migrate         # Run pending migrations
make migration       # Generate migration from entity diff
make db-reset        # Drop → create → migrate  ⚠ destructive
make fixtures        # Load fixtures

# Quality (run in order)
make qa              # phpcbf → phpcs → phpstan → deptrac (all in one)
make phpcbf          # Auto-fix PHP style
make phpcs           # Check PHP style
make phpstan         # Static analysis (level 10)
make deptrac         # Architecture boundary validation
make type-check      # vue-tsc TypeScript check

# Tests
make test            # Full test suite
make test-filter f=MyTest   # Run a specific test
```

---

## Architecture

The backend follows **Hexagonal Architecture** (Ports & Adapters) with DDD bounded contexts.

```
Infrastructure  ──▶  Application  ──▶  Domain
   (adapters)         (use cases)     (pure PHP)
```

- **Domain** — pure PHP, zero framework dependencies. Entities, Value Objects, Domain Events, Repository interfaces.
- **Application** — CQRS via Symfony Messenger. Commands mutate state; Queries return typed DTOs. Handlers are thin orchestrators.
- **Infrastructure** — Doctrine repositories, Symfony controllers, Messenger consumers, external HTTP clients.

Each bounded context (`Identity`, `Cat`, `Calendar`, `ChipType`) is a self-contained namespace under `src/`.

The frontend follows **Atomic Design**: `atoms` → `molecules` → `organisms` → `templates` → `pages`. Server state lives in TanStack Query; UI state lives in Pinia.

---

## Quality Gates

All gates must pass before a PR is merged.

| Gate | Tool | Command |
|---|---|---|
| Static analysis | PHPStan level 10 | `make phpstan` |
| Code style | PHP_CodeSniffer (PSR-12) | `make phpcs` |
| Architecture | Deptrac | `make deptrac` |
| TypeScript | vue-tsc | `make type-check` |
| Tests | PHPUnit 11 | `make test` |

---

## Testing

Tests mirror the `src/` structure under `tests/`.

- **Unit** — Domain models, Value Objects, Domain Services. Use in-memory fakes, never hit the DB.
- **Integration** — Application handlers via `KernelTestCase`.
- **Functional** — HTTP endpoints via `WebTestCase` + DAMA Doctrine Bundle (auto transaction rollback). Authenticated endpoints extend `AuthenticatedWebTestCase`.

Every new feature requires at minimum one happy-path and one error-path functional test.

---

## User Roles

| Role | Capabilities |
|---|---|
| **Owner** | Full access to their cats, calendars, chip types, and pet sitter invitations |
| **Pet Sitter** | Read/write access scoped to the cats of the owner who invited them |
| **Admin** | Platform-wide management (deferred) |

---

## License

Private project — all rights reserved.
