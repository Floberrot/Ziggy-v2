# Plan: Owner Profile Page + Enhanced Pet Sitter CRUD

## Context
Owners need a dedicated profile page showing personal data (age, phone) and usage stats (cat count, chips placed). They also need richer pet sitter management: type classification (Family/Friend/Professional), age and phone records per sitter, full CRUD, and an invitation decline flow. Pet sitters must be blocked from the owner profile and pet sitter management pages.

---

## Architecture decisions

1. **`PetSitter` aggregate** in Identity domain — owns type/age/phone/inviteeEmail/userId(nullable). Linked to Invitation via `(ownerId, inviteeEmail)` join in Infrastructure (no FK, avoids circular coupling).
2. **`OwnerProfile` mutable model** in Identity domain — stores owner's age and phone. Upserted on first update; GET returns zero-value defaults if none exists.
3. **`Invitation.declined`** — add `declined: bool` flag + `decline()` domain method with invariant checks.
4. **`OwnerStatsPort`** in Identity Application — returns `catsCount` + `chipsCount` via DBAL cross-context queries (cats table by `owner_id`, chips table by `author_id`).
5. **`CreatePetSitter` command** creates both PetSitter record + Invitation in one handler. Existing `SendInvitation` endpoint is untouched.
6. **`AcceptInvitationHandler` modified** — after creating the user, find PetSitter by `(ownerId, inviteeEmail)` and link their `userId`.
7. **Route protection** — `/profile` and `/pet-sitters` pages check `me.role` via TanStack Query on mount; redirect to `/dashboard` if `ROLE_PET_SITTER`.
8. **Decline endpoint** at `POST /api/auth/invitation/decline` (token in body) — PUBLIC_ACCESS via existing security.yaml rule on `^/api/auth/invitation/`.

---

## New API Endpoints

| Method | Path | Auth |
|--------|------|------|
| GET | `/api/profile` | ROLE_USER |
| PUT | `/api/profile` | ROLE_USER |
| POST | `/api/pet-sitters` | ROLE_USER |
| GET | `/api/pet-sitters` | ROLE_USER |
| PUT | `/api/pet-sitters/{id}` | ROLE_USER |
| DELETE | `/api/pet-sitters/{id}` | ROLE_USER |
| POST | `/api/auth/invitation/decline` | PUBLIC |

---

## Files to create / modify

### Domain — `back/src/Identity/Domain/`

| Status | File | Notes |
|--------|------|-------|
| NEW | `Model/PetSitter.php` | Mutable aggregate: `id`, `ownerId`, `inviteeEmail`, `userId?`, `type`, `age?`, `phoneNumber?`, `createdAt`. Methods: `linkUser(UserId)`, `updateData(PetSitterType, ?int, ?string)` |
| NEW | `Model/PetSitterId.php` | Value object wrapping UUID string |
| NEW | `Model/PetSitterType.php` | Backed enum: `Family = 'family'`, `Friend = 'friend'`, `Professional = 'professional'` |
| NEW | `Model/OwnerProfile.php` | Mutable model: `userId`, `age?`, `phoneNumber?`. Method: `update(?int, ?string)` |
| NEW | `Repository/PetSitterRepository.php` | `save`, `findById`, `findByOwnerId`, `findByOwnerAndEmail`, `remove` |
| NEW | `Repository/OwnerProfileRepository.php` | `save`, `findByUserId` |
| NEW | `Exception/PetSitterNotFoundException.php` | Extends `NotFoundException` |
| NEW | `Exception/InvitationAlreadyDeclinedException.php` | Extends `BusinessRuleException` |
| MOD | `Model/Invitation.php` | Add `declined: bool` to constructor, `create()`, `reconstruct()`. Add `decline()` method (guards: already accepted → `InvitationAlreadyAcceptedException`, expired → `InvitationExpiredException`, already declined → `InvitationAlreadyDeclinedException`). Add `isDeclined()` accessor. |

### Application — `back/src/Identity/Application/`

| Status | File | Notes |
|--------|------|-------|
| NEW | `Command/CreatePetSitter/CreatePetSitterCommand.php` | `ownerEmail`, `inviteeEmail`, `catId`, `type`, `age?`, `phoneNumber?` |
| NEW | `Command/CreatePetSitter/CreatePetSitterHandler.php` | Creates PetSitter + Invitation, calls `InvitationMailer::sendInvitation()` |
| NEW | `Command/UpdatePetSitter/UpdatePetSitterCommand.php` | `petSitterId`, `ownerEmail`, `type`, `age?`, `phoneNumber?` |
| NEW | `Command/UpdatePetSitter/UpdatePetSitterHandler.php` | Find by id, assert owner matches, call `updateData()`, save |
| NEW | `Command/RemovePetSitter/RemovePetSitterCommand.php` | `petSitterId`, `ownerEmail` |
| NEW | `Command/RemovePetSitter/RemovePetSitterHandler.php` | Find, assert owner, remove PetSitter + remove pending invitations via `InvitationRepository` |
| NEW | `Command/UpdateOwnerProfile/UpdateOwnerProfileCommand.php` | `ownerEmail`, `age?`, `phoneNumber?` |
| NEW | `Command/UpdateOwnerProfile/UpdateOwnerProfileHandler.php` | Upsert OwnerProfile |
| NEW | `Command/DeclineInvitation/DeclineInvitationCommand.php` | `token` |
| NEW | `Command/DeclineInvitation/DeclineInvitationHandler.php` | Find by token, call `decline()`, save via `InvitationRepository::save()` |
| NEW | `Query/GetOwnerProfile/GetOwnerProfileQuery.php` | `ownerEmail` |
| NEW | `Query/GetOwnerProfile/GetOwnerProfileHandler.php` | Find user, find/default profile, call `OwnerStatsPort`, return `OwnerProfileView` |
| NEW | `Query/GetOwnerProfile/OwnerProfileView.php` | `userId`, `email`, `username?`, `age?`, `phoneNumber?`, `catsCount`, `chipsCount` |
| NEW | `Query/ListPetSitters/ListPetSittersQuery.php` | `ownerEmail` |
| NEW | `Query/ListPetSitters/ListPetSittersHandler.php` | Fetch all pet sitters + all invitations for owner, join by email in PHP, return `list<PetSitterView>` |
| NEW | `Query/ListPetSitters/PetSitterView.php` | `id`, `inviteeEmail`, `userId?`, `type`, `age?`, `phoneNumber?`, `invitations: list<PetSitterInvitationView>` |
| NEW | `Query/ListPetSitters/PetSitterInvitationView.php` | `id`, `catId`, `token`, `accepted`, `declined`, `expired` |
| NEW | `Port/OwnerStatsPort.php` | `countCatsByOwnerId(string): int`, `countChipsByOwnerId(string): int` |
| MOD | `Command/AcceptInvitation/AcceptInvitationHandler.php` | Inject `PetSitterRepository`; after saving user, call `findByOwnerAndEmail()` → if found, call `linkUser()` → save |

### Infrastructure — `back/src/Identity/Infrastructure/`

| Status | File | Notes |
|--------|------|-------|
| NEW | `Output/Persistence/PetSitterOrmEntity.php` | Maps `pet_sitters` table |
| NEW | `Output/Persistence/DoctrinePetSitterRepository.php` | Implements `PetSitterRepository` |
| NEW | `Output/Persistence/OwnerProfileOrmEntity.php` | Maps `owner_profiles` table |
| NEW | `Output/Persistence/DoctrineOwnerProfileRepository.php` | Implements `OwnerProfileRepository` |
| NEW | `Output/Persistence/DoctrineOwnerStatsProvider.php` | Implements `OwnerStatsPort` via DBAL; `COUNT(id) FROM cats WHERE owner_id = ?`, `COUNT(id) FROM chips WHERE author_id = ?` |
| MOD | `Output/Persistence/InvitationOrmEntity.php` | Add `$declined = false` column |
| MOD | `Output/Persistence/DoctrineInvitationRepository.php` | Add `markDeclined(string $token)`, update `save()` + `toDomain()` to include `declined`, add `findPendingByOwnerAndEmail()` for RemovePetSitter |
| NEW | `Input/Http/CreatePetSitterController.php` | `POST /api/pet-sitters` |
| NEW | `Input/Http/ListPetSittersController.php` | `GET /api/pet-sitters` |
| NEW | `Input/Http/UpdatePetSitterController.php` | `PUT /api/pet-sitters/{id}` |
| NEW | `Input/Http/RemovePetSitterController.php` | `DELETE /api/pet-sitters/{id}` |
| NEW | `Input/Http/GetOwnerProfileController.php` | `GET /api/profile` |
| NEW | `Input/Http/UpdateOwnerProfileController.php` | `PUT /api/profile` |
| NEW | `Input/Http/DeclineInvitationController.php` | `POST /api/auth/invitation/decline` — no auth (matches existing `PUBLIC_ACCESS` on `^/api/auth/invitation/`) |
| NEW | `Input/Http/Request/CreatePetSitterRequest.php` | `inviteeEmail`, `catId`, `type`, `age?`, `phoneNumber?` |
| NEW | `Input/Http/Request/UpdatePetSitterRequest.php` | `type`, `age?`, `phoneNumber?` |
| NEW | `Input/Http/Request/UpdateOwnerProfileRequest.php` | `age?`, `phoneNumber?` |
| NEW | `Input/Http/Request/DeclineInvitationRequest.php` | `token` |
| NEW | `back/migrations/Version20260401000000.php` | Creates `pet_sitters`, `owner_profiles`; adds `declined BOOLEAN DEFAULT FALSE` to `invitations` |

### Frontend — `front/assets/`

| Status | File | Notes |
|--------|------|-------|
| NEW | `api/profile.ts` | `getProfile()`, `updateProfile(data)` |
| NEW | `api/petSitters.ts` | `list()`, `create(data)`, `update(id, data)`, `remove(id)` |
| MOD | `api/invitations.ts` | Add `decline(token: string): Promise<void>` |
| MOD | `types/index.ts` | Add `PetSitterType`, `PetSitter`, `PetSitterInvitationItem`, `OwnerProfile`; update `Invitation` with `declined: boolean` |
| NEW | `components/pages/ProfilePage.vue` | Fetch `me` + `profile`; redirect to `/dashboard` if `ROLE_PET_SITTER`. Show age/phone form + stats (cats count, chips count) |
| NEW | `components/organisms/PetSitterRow.vue` | Displays one pet sitter row: email, type badge, age, phone, invitation statuses (per-cat chips), edit / remove actions |
| MOD | `components/pages/PetSittersPage.vue` | Full rewrite: role guard, fetch from `/api/pet-sitters`, datatable with `PetSitterRow`, create modal (adds type/age/phone/cat selectors), inline edit |
| MOD | `components/pages/AcceptInvitationPage.vue` | Add "Decline invitation" button calling `invitationsApi.decline(token)` → redirect to `/login?declined=1` |
| MOD | `router/index.ts` | Add `/profile` route with `requiresAuth: true` |
| MOD | `components/pages/DashboardPage.vue` | Add "My Profile" card to sections list (shown to everyone but guarded in page) |

---

## Quality risks

- **PHPStan**: `PetSitter.userId` is nullable — every access site must null-check. `OwnerStatsPort` DBAL queries return mixed; cast to `(int)` explicitly.
- **Deptrac**: `DoctrineOwnerStatsProvider` uses DBAL (not Cat/Calendar domain models) — safe, stays in Infrastructure.
- **PHPStan**: `Invitation::decline()` throws three different exceptions — all must be declared/thrown from domain model and re-caught by exception listener.
- **Vue TS**: `PetSitterType` string union must match backend enum values exactly (`'family'`, `'friend'`, `'professional'`).

---

## Verification

1. `vendor/bin/phpcbf && vendor/bin/phpcs` — zero violations
2. `vendor/bin/phpstan analyse` — zero errors
3. `vendor/bin/deptrac analyse` — zero violations
4. `npm run type-check` — zero TypeScript errors
5. Manual: register owner → invite pet sitter → log in as pet sitter → verify `/profile` and `/pet-sitters` redirect to dashboard
6. Manual: owner creates pet sitter with type Family, age 30, phone → appears in datatable → edit to Professional → saved
7. Manual: pet sitter receives invitation link → clicks decline → invitation shows "Declined" in owner datatable
8. Manual: owner profile page shows correct cat count and chip count
