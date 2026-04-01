# Plan: Quality Gates + Tests + CI

## Context
The project has PHPStan, PHPCS, and Deptrac configured but no PHPUnit tests exist (tests/ only has .gitkeep + object-manager.php). The GitHub Actions CI checks code style, architecture, and migrations — but never runs tests, meaning regressions can merge unchecked. Goal: fix any current quality violations, add a full test suite (unit → integration → functional/e2e), and add a required tests job to CI.

---

## Step 1 — Fix existing quality violations

Run gates sequentially inside the `back/` directory and fix all errors:

```bash
vendor/bin/phpcbf        # auto-fix style
vendor/bin/phpcs         # confirm zero remaining
vendor/bin/phpstan analyse
vendor/bin/deptrac analyse
```

**Known risks:**
- `ExceptionListener` catches bare `\DomainException` and `\InvalidArgumentException` — PHPStan may flag these as redundant or too broad (check CLAUDE.md rule: every exception must be unique; middleware handles all).
- `PlaceChipHandler` builds a date by combining a string with `now()` — could trigger PHPStan mixed-type warnings.
- Any file not following PSR-12 120-char line limit will be flagged.

---

## Step 2 — Add PHPUnit + testing dependencies

**Modify:** `back/composer.json` — add to `require-dev`:
```json
"phpunit/phpunit": "^11",
"symfony/test-pack": "*",
"dama/doctrine-test-bundle": "^8"
```

`symfony/test-pack` pulls in: `browser-kit`, `dom-crawler`, `phpunit-bridge` (provides `bin/phpunit`).
`dama/doctrine-test-bundle` wraps each functional test in a rolled-back transaction — keeps the test DB clean without truncating tables.

---

## Step 3 — Configure PHPUnit

**Create:** `back/phpunit.xml.dist`
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" colors="true" stopOnError="false">
    <php>
        <ini name="display_errors" value="1"/>
        <env name="APP_ENV" value="test"/>
        <env name="KERNEL_CLASS" value="App\Kernel"/>
        <env name="DATABASE_URL" value="postgresql://ziggy:ziggy@127.0.0.1:5432/ziggy_test?serverVersion=16"/>
    </php>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Functional">
            <directory>tests/Functional</directory>
        </testsuite>
    </testsuites>
    <extensions>
        <bootstrap class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>
    </extensions>
    <coverage>
        <include>
            <directory>src</directory>
        </include>
    </coverage>
</phpunit>
```

**Register bundle** in `config/bundles.php` (test env only):
```php
DAMA\DoctrineTestBundle\DAMADoctrineTestBundle::class => ['test' => true],
```

---

## Step 4 — Test directory structure

```
tests/
├── object-manager.php              (already exists — PHPStan Doctrine loader)
├── Shared/
│   └── InMemory/                   (reusable in-memory repository fakes)
│       ├── InMemoryUserRepository.php
│       ├── InMemoryInvitationRepository.php
│       ├── InMemoryPasswordResetTokenRepository.php
│       ├── InMemoryCatRepository.php
│       ├── InMemoryCalendarRepository.php
│       └── InMemoryChipTypeRepository.php
├── Unit/
│   ├── Calendar/Domain/Model/
│   │   ├── CalendarTest.php          placeChip records event, removeChip works
│   │   └── ChipTest.php              updateNote mutation
│   ├── Cat/Domain/Model/
│   │   └── CatTest.php               Cat::create, CatName validation
│   ├── ChipType/Domain/Model/
│   │   └── ChipTypeTest.php          ChipType::create, ChipColor validation
│   └── Identity/Domain/Model/
│       ├── UserTest.php              User::register records event, updateUsername, changePassword
│       └── EmailTest.php             Email value object validation
├── Integration/
│   ├── Calendar/Application/Command/
│   │   ├── PlaceChipHandlerTest.php
│   │   └── RemoveChipHandlerTest.php
│   ├── Cat/Application/Command/
│   │   ├── AddCatHandlerTest.php
│   │   └── RemoveCatHandlerTest.php
│   └── Identity/Application/Command/
│       ├── RegisterOwnerHandlerTest.php
│       └── AcceptInvitationHandlerTest.php
└── Functional/
    ├── Identity/
    │   ├── RegisterOwnerControllerTest.php
    │   ├── AcceptInvitationControllerTest.php
    │   └── MeControllerTest.php
    ├── Cat/
    │   ├── AddCatControllerTest.php
    │   ├── ListCatsControllerTest.php
    │   └── RemoveCatControllerTest.php
    └── Calendar/
        ├── PlaceChipControllerTest.php
        └── GetCalendarControllerTest.php
```

---

## Step 5 — In-memory repositories (for Integration tests)

Each implements the Domain repository interface. Example:

```php
// tests/Shared/InMemory/InMemoryUserRepository.php
namespace App\Tests\Shared\InMemory;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<string, User> */
    private array $store = [];

    public function save(User $user): void
    {
        $this->store[$user->getId()->value()] = $user;
    }

    public function findById(UserId $id): ?User
    {
        return $this->store[$id->value()] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->store as $user) {
            if ($user->getEmail()->value() === $email->value()) {
                return $user;
            }
        }
        return null;
    }
}
```

---

## Step 6 — Integration tests

Use `KernelTestCase` (lightweight kernel boot, no HTTP) — simpler than custom stubs for Symfony-injected services like the password hasher.

```php
final class RegisterOwnerHandlerTest extends KernelTestCase
{
    public function testRegistersOwnerSuccessfully(): void
    {
        // Boot kernel, get handler from container, dispatch command, assert DB state
    }

    public function testThrowsWhenEmailAlreadyRegistered(): void
    {
        $this->expectException(EmailAlreadyRegisteredException::class);
        // dispatch same command twice
    }
}
```

---

## Step 7 — Functional (E2E) tests

Use `WebTestCase` + DAMA bundle (auto transaction rollback).

```php
final class RegisterOwnerControllerTest extends WebTestCase
{
    public function testRegisterReturns201(): void { ... }
    public function testRegisterReturns422WithMissingEmail(): void { ... }
    public function testRegisterReturns409WhenEmailTaken(): void { ... }
}
```

Authenticated endpoints need a `AuthenticatedWebTestCase` base class with a `getAuthToken()` helper (registers + logs in, caches the JWT).

---

## Step 8 — Update GitHub Actions CI

**Modify:** `.github/workflows/ci.yml` — add `tests` job after `migrations`:

```yaml
tests:
  name: PHPUnit Tests
  runs-on: ubuntu-latest
  needs: migrations
  services:
    postgres:
      image: postgres:16
      env:
        POSTGRES_USER: ziggy
        POSTGRES_PASSWORD: ziggy
        POSTGRES_DB: ziggy_test
      # health check + port 5432
  steps:
    # checkout, setup-php (pdo_pgsql), cache, composer install
    - name: Generate JWT keys
    - name: Run migrations (test DB)
      env:
        DATABASE_URL: postgresql://ziggy:ziggy@127.0.0.1:5432/ziggy_test?serverVersion=16&charset=utf8
      run: php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    - name: Run PHPUnit
      env:
        DATABASE_URL: postgresql://ziggy:ziggy@127.0.0.1:5432/ziggy_test?serverVersion=16&charset=utf8
      run: php bin/phpunit --no-progress
```

---

## Files to create/modify

| File | Action |
|------|--------|
| `back/composer.json` | Add phpunit, test-pack, dama bundle |
| `back/phpunit.xml.dist` | Create |
| `back/config/bundles.php` | Register DAMADoctrineTestBundle for test env |
| `back/.env.test` | Ensure DATABASE_URL points to `ziggy_test` |
| `.github/workflows/ci.yml` | Add tests job |
| `back/tests/Shared/InMemory/*.php` | Create 6 in-memory repos |
| `back/tests/Unit/**/*Test.php` | Create ~6 unit test files |
| `back/tests/Integration/**/*Test.php` | Create ~5 integration test files |
| `back/tests/Functional/**/*Test.php` | Create ~8 functional test files |
| Source files in `src/` | Fix any phpstan/phpcs/deptrac violations found |

---

## Verification

1. `vendor/bin/phpcs` → 0 violations
2. `vendor/bin/phpstan analyse` → 0 errors
3. `vendor/bin/deptrac analyse` → 0 violations
4. `php bin/phpunit` → all green
5. Push to a branch → all 5 CI jobs pass (phpcs, deptrac, migrations, phpstan, tests)
