---
name: plan-and-ship
description: Full plan-to-PR workflow. When invoked, enter plan mode, get approval, implement, run all quality gates (PHPStan + PHP CS Fixer + Deptrac), then checkout a branch, commit, push, and open a PR.
metadata:
  author: floberrot
  version: "1.0.0"
---

# Plan and Ship Workflow

This skill governs the full lifecycle from planning to a merged-ready pull request. Follow every step in order. Do not skip steps. Do not open a PR if any quality gate fails.

---

## Step 1 — Enter Plan Mode (required)

Before writing a single line of code:

1. Use `EnterPlanMode` to present a structured implementation plan.
2. The plan must include:
   - **Goal**: one sentence describing what will be built or fixed.
   - **Bounded context(s)** affected.
   - **Files to create or modify**, grouped by layer (Domain / Application / Infrastructure / Frontend).
   - **Architecture decisions** (new ports, new exceptions, new events, etc.).
   - **Quality risks** (anything that might trip PHPStan or Deptrac).
3. Wait for explicit user approval before proceeding. Do not start coding until the user says yes (or an equivalent confirmation).

---

## Step 2 — Implement

Follow the approved plan exactly. Apply all rules from `CLAUDE.md`, `backend.md`, `frontend.md`, and `quality.md`:

- Hexagonal architecture: Domain → Application → Infrastructure.
- `final readonly` classes and explicit types everywhere.
- No French. No FQCN inline. No mixed types.
- Controllers use `#[MapRequestPayload]`; exceptions are unique; middleware handles all exceptions.
- Vue components follow Atomic Design with `<script setup lang="ts">`.

---

## Step 3 — Quality Gates (all must pass, in order)

Run the following commands sequentially. Fix every error before moving to the next tool. Do not proceed to Step 4 until all three exit with code 0.

### 3.1 PHP CS Fixer (auto-fix first)
```bash
vendor/bin/phpcbf
vendor/bin/phpcs
```
- Run `phpcbf` to auto-fix style violations.
- Then run `phpcs` to confirm zero remaining violations.
- If `phpcs` still reports errors: fix them manually, then re-run `phpcs`.

### 3.2 PHPStan — Level 10
```bash
vendor/bin/phpstan analyse
```
- Zero errors allowed.
- Every `@phpstan-ignore` requires a comment explaining why.

### 3.3 Deptrac — Architecture Boundaries
```bash
vendor/bin/deptrac analyse
```
- Zero violations allowed.
- Domain must import nothing from Application or Infrastructure.
- Application must import nothing from Infrastructure.

### 3.4 Vue type check (if frontend files were changed)
```bash
npm run type-check
```
- Zero TypeScript errors allowed.

---

## Step 4 — Branch, Commit, Push, PR

Only proceed here when **all quality gates in Step 3 passed with zero errors**.

### 4.1 Create or switch to a feature branch
Branch naming convention: `<type>/<short-kebab-description>`

Types: `feat`, `fix`, `refactor`, `chore`, `test`, `docs`

```bash
git checkout -b feat/my-feature-name
```

If a branch already exists for this work, switch to it instead of creating a new one.

### 4.2 Stage and commit
- Stage only files related to the current task. Never `git add -A` blindly.
- Commit message format (Conventional Commits):

```
<type>(<scope>): <short imperative description>

<optional body — what and why, not how>

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
```

Examples:
- `feat(user): add email verification on registration`
- `fix(order): prevent duplicate placement on retry`
- `refactor(auth): extract token validation to domain service`

### 4.3 Push
```bash
git push -u origin <branch-name>
```

### 4.4 Open a Pull Request
Use `gh pr create` with a descriptive title and body:

```bash
gh pr create \
  --title "<type>(<scope>): <description>" \
  --body "$(cat <<'EOF'
## Summary
- <bullet 1>
- <bullet 2>

## Changes
- **Domain**: <what changed>
- **Application**: <what changed>
- **Infrastructure**: <what changed>
- **Frontend**: <what changed, or "N/A">

## Quality gates
- [x] `phpcs` — 0 violations
- [x] `phpstan` — 0 errors (level 10)
- [x] `deptrac` — 0 violations
- [x] `vue-tsc` — 0 errors (if applicable)

## Test plan
- [ ] <manual test step 1>
- [ ] <manual test step 2>

🤖 Generated with [Claude Code](https://claude.com/claude-code)
EOF
)"
```

Return the PR URL to the user.

---

## Rules

- **Never skip Step 1.** Planning before coding is mandatory.
- **Never open a PR with failing quality gates.** Fix first, ship second.
- **Never force-push** to `main`/`master`.
- **Never use `--no-verify`** to bypass hooks.
- **Never commit unrelated files** (`.env`, lock files not part of the change, IDE files).
- If a quality gate fails after multiple fix attempts, stop and ask the user for guidance rather than suppressing errors.
