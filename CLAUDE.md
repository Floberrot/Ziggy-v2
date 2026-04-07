# Ziggy — Project Guide

Stack: **Symfony UX · Vue 3 · FrankenPHP · Docker**

---

## Git Discipline

- **One commit per PR** — a PR must land as a single commit. Prefer `git commit --amend` to add changes to the current commit; use `git rebase -i` to squash only if amend is not possible.
- **Never create a new commit when one already exists on the branch** — always amend instead.
- **Commits must be authored solely by the repo owner** — never set Claude or any AI assistant as the author. Always preserve the user's git identity (`user.name` / `user.email`). Never pass `--author` or alter git config.

## Sub-files

@.claude/backend.md
@.claude/frontend.md
@.claude/quality.md
@.claude/skills.md

---

## Global Rules

### Architecture
- **Dependencies point inward**: Infrastructure → Application → Domain
- **Domain is pure PHP**: no Symfony, no Doctrine, no framework anywhere in Domain
- **One bounded context per top-level namespace** under `src/`
- **Commands mutate state; Queries return data** — never mix the two
- **Handlers are thin**: orchestrate only, delegate logic to Domain services injected via DI
- **A handler performs exactly one action — the one its name describes** (e.g. `RegisterOwnerHandler` registers the owner, nothing else). Any subsequent side-effect (send email, update a related aggregate, notify, log a business event) must be triggered by a Domain Event and handled in a dedicated `EventHandler`. Never chain multiple responsibilities inside a single handler.
- **Read models are separate from write models**: Query handlers return DTOs, not entities
- **Domain Events are dispatched after persistence**, never before
- Do not create DomainEvents if no listener uses them — all code must be used and clean
- **Response of query handler must always be a typed object or array** — `toArray()` logic belongs to the Domain model, not the handler

### Code Style
- **Never use FQCN inline** — always import classes with `use` statements at the top of the file
- **All code must be written in English** — absolutely no French in any line of code, comment, or string
- Return types, parameter types, and property types must all be explicit
- Use `final` classes wherever possible
- Use `readonly` on class level when all properties are readonly — do not add `readonly` to individual properties of a `readonly class`
- No mixed types anywhere

### Controllers
- **Controllers must use `#[MapRequestPayload]`** to receive a typed request object — never parse `$request->getContent()` manually
- No logic in controllers — dispatch only; errors are handled by the exception middleware
- No `try/catch` in controllers for domain exceptions

### Exceptions
- **Every exception must be a distinct class** — never reuse `DomainException` or throw base exceptions directly
- Shared abstract exceptions (e.g. `NotFoundException`, `BusinessRuleException`) may live in `Shared/Domain/Exception/`
- **A middleware catches every thrown exception and returns the appropriate HTTP response**
- **Every exception must be logged in the middleware**

### PHPStan / Quality
- No mixed types, no ignored errors without justification
- Every `@phpstan-ignore` must have a comment explaining why
- All code quality checks (PHPStan, PHPCS, Deptrac) must pass before any PR is merged

---

## Testing Requirements

**Every new feature must be covered by tests. No exception.**

### Rules
- **Functional tests are mandatory** for every new feature — at minimum one happy path and one error path per endpoint
- **Unit tests are strongly preferred** for Domain models, Value Objects, and Domain Services — test invariants, state transitions, and validations
- **Integration tests** are expected for complex Application handlers (use `KernelTestCase`)
- Tests must live in `tests/Unit/`, `tests/Integration/`, or `tests/Functional/` mirroring the `src/` structure
- Use in-memory repository fakes (in `tests/Shared/InMemory/`) for unit and integration tests — never hit the real database in unit tests
- Functional tests use `WebTestCase` + DAMA Doctrine Test Bundle (auto transaction rollback)
- Authenticated endpoints require an `AuthenticatedWebTestCase` base class with a `getAuthToken()` helper
- **Running `php bin/phpunit` must pass before any PR is merged**

### Test structure
```
tests/
├── Shared/
│   └── InMemory/          # In-memory repository fakes implementing Domain interfaces
├── Unit/
│   └── <BoundedContext>/Domain/   # Domain model, value object, service tests
├── Integration/
│   └── <BoundedContext>/Application/  # Handler tests via KernelTestCase
└── Functional/
    └── <BoundedContext>/          # HTTP controller tests via WebTestCase
```

---

## Mandatory Skill Invocations

Claude **must** invoke the following skills without being asked when the context matches.

### Plan & Ship
When the user says **"make a plan and execute it"**, **"plan and ship"**, or any equivalent:
→ **invoke `plan-and-ship`**

Full lifecycle enforced:
1. Enter plan mode — structured plan, wait for approval
2. Implement following all architecture, quality, and testing rules
3. Quality gates in order: `phpcbf` → `phpcs` → `phpstan` → `deptrac` → `vue-tsc` (if frontend touched) → `php bin/phpunit`
4. Checkout feature branch, commit (Conventional Commits), push, open PR

**Never open a PR if any quality gate or test fails.**

### Symfony UX — Frontend UI

| Context | Skill to invoke |
|---|---|
| Pure client-side JS behavior, no server round-trip | `stimulus` |
| Navigation, partial page updates, Turbo Frames/Streams | `turbo` |
| Reusable static server-rendered UI component (Twig) | `twig-component` |
| Reactive component re-rendering on user interaction | `live-component` |
| SVG icons (local or Iconify) | `ux-icons` |
| Interactive maps | `ux-map` |
| Unsure which Symfony UX tool fits | `symfony-ux` |

### Vue.js

| Context | Skill to invoke |
|---|---|
| Any `.vue` file work | `vue-best-practices` — **always load** |
| Composable (shared logic, `use*` prefix) | `create-adaptable-composable` |
| Pinia store work | `vue-pinia-best-practices` |
| Vue Router guards or navigation | `vue-router-best-practices` |
| Vue component or composable testing | `vue-testing-best-practices` |
| Debugging a Vue runtime error or warning | `vue-debug-guides` |

### Infrastructure & DevOps

| Context | Skill to invoke |
|---|---|
| FrankenPHP config, workers, real-time | `frankenphp` |
| Dockerfile, multi-stage build, docker-compose | `docker-containerization` |
| Docker CLI commands | `docker-cli` |

### Update Coding Rules
When a bad practice is spotted, a recurring mistake is identified, a rule is missing, or the user says **"add this to the rules"**, **"we should always/never"**, or **"make sure we don't do this again"**:
→ **invoke `update-coding-rules`**

---

## Architectural Reminders (quick reference)

- `Domain` → nothing
- `Application` → `Domain` only
- `Infrastructure` → `Application` + `Domain`
- SRP first: one class, one responsibility; inject everything via DI
- Handler = orchestrator only; real logic lives in Domain Services
