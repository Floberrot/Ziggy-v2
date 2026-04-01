# Ziggy — Project Guide

Stack: **Symfony UX · Vue 3 · FrankenPHP · Docker**

## Global Rules

- **Never use FQCN inline** — always import classes with `use` statements at the top of the file
- All code quality checks (PHPStan, PHPCS, Deptrac) must pass before any PR is merged
- No mixed types, no ignored errors without justification
- Every `@phpstan-ignore` must have a comment explaining why
- Return types, parameter types, and property types must all be explicit
- Use `readonly` properties and `final` classes wherever possible. `readonly` on props is useless if the class can be `readonly`.
- `Domain` depends on nothing — zero external layer imports
- `Application` may only depend on `Domain`
- `Infrastructure` may depend on `Application` and `Domain`
- Any violation = build failure; never suppress without a documented reason
- **Dependencies point inward**: Infrastructure → Application → Domain
- **Domain is pure PHP**: no Symfony, no Doctrine, no framework anywhere in Domain
- **Commands mutate state; Queries return data** — never mix the two
- **Domain Events are dispatched after persistence**, never before
- **Handlers are thin**: orchestrate only, delegate logic to the Domain
- **Read models are separate from write models**: Query handlers return DTOs, not entities
- **One bounded context per top-level namespace** under `src/`
- **Never use FQCN inline** — always import classes with `use` statements at the top of the file
- **Controller should have a MapRequestPayload in entry to have a typed object request**
- **Every exception should be different. No `DomainException` re-use every where. In Shared there can be abstract exception such as NotFoundException.**
- **A middleware listen every Exception thrown an return a specific response with this exception.**
- **Every exception should be logged in the middleware**
- **Absolutely never line of codes should be written in French**
- **Response of query handler should ALWAYS be object typed OR array but the to Array function should be implements Domain models. The response logic belongs to Domain.**
- **Readonly properties are useless if class is readonly.**
- Do not create DomainEvent if no listener used them. All code MUST be clear, used and clean.
- SRP should be main thing. A handler should not do anything. Create as much as you can services/ports and call them with DI. 

## Sub-files

@.claude/backend.md
@.claude/frontend.md
@.claude/quality.md
@.claude/skills.md
