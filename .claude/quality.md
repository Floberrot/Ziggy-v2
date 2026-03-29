# Code Quality Tools

All checks must pass before opening a PR. A PR with any violation must not be merged.

```bash
vendor/bin/phpstan analyse       # static analysis
vendor/bin/phpcs                 # code style check
vendor/bin/phpcbf                # auto-fix style
vendor/bin/deptrac analyse       # architecture boundaries
```

---

## PHPStan — Level 10

Static analysis at the strictest level. Zero errors allowed.

```neon
# phpstan.neon
parameters:
    level: 10
    paths:
        - src
    symfony:
        container_xml_path: var/cache/dev/App_KernelDevDebugContainer.xml
```

**Rules:**
- No mixed types, no ignored errors without justification
- Every `@phpstan-ignore` must have a comment explaining why
- Return types, parameter types, and property types must all be explicit
- Use `readonly` properties and `final` classes wherever possible

**Run:** `vendor/bin/phpstan analyse`

---

## PHP_CodeSniffer — PSR-12

Enforces consistent code style across the entire codebase.

```xml
<!-- phpcs.xml -->
<?xml version="1.0"?>
<ruleset name="Ziggy">
    <arg name="basepath" value="."/>
    <arg name="extensions" value="php"/>
    <arg name="parallel" value="8"/>
    <file>src</file>
    <rule ref="PSR12"/>
</ruleset>
```

**Rules:**
- PSR-12 is the baseline — no exceptions
- Run `phpcbf` to auto-fix before running `phpcs` to check
- 4 spaces for indentation, no tabs
- Opening braces on the same line for classes and methods

**Run:** `vendor/bin/phpcs` / auto-fix: `vendor/bin/phpcbf`

---

## Deptrac — Architecture Enforcement

Enforces hexagonal architecture layer boundaries. Prevents illegal dependencies between layers.

```yaml
# deptrac.yaml
deptrac:
    paths:
        - src
    layers:
        - name: Domain
          collectors:
              - type: directory
                value: src/.*/Domain/.*
        - name: Application
          collectors:
              - type: directory
                value: src/.*/Application/.*
        - name: Infrastructure
          collectors:
              - type: directory
                value: src/.*/Infrastructure/.*
    ruleset:
        Domain: []
        Application:
            - Domain
        Infrastructure:
            - Application
            - Domain
```

**Rules:**
- `Domain` depends on nothing — zero external layer imports
- `Application` may only depend on `Domain`
- `Infrastructure` may depend on `Application` and `Domain`
- Any violation = build failure; never suppress without a documented reason

**Run:** `vendor/bin/deptrac analyse`
