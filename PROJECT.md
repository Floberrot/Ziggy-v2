# Ziggy — Project Description

Ziggy is a web application that manages a calendar per cat. Place chips on each day to track things to do for your kitty.

---

## User Roles

### Owner (`ROLE_USER`)

The main actor. Owns cats and drives all write operations.

**Profile & Account**
- Register an account (email + password)
- Accept an invitation sent by admin to activate account
- Update profile: username, age, phone number
- View personal stats: number of cats, number of chips placed

**Cat Management**
- Add a cat (name, breed, color, weight, birth date)
- Update a cat's data
- Remove a cat
- View cat weight history

**ChipType Management**
- Create chip types (label + color)
- Update chip types
- Remove chip types
- List all chip types

**Calendar**
- Each cat has its own calendar automatically
- Place a chip on a day (type + note + date)
- Remove a chip from a calendar
- View the full calendar for a cat (week/day view)

**Pet Sitter Management**
- Invite a person as pet sitter for a specific cat (sends invitation by email)
- Classify pet sitters by type: Family / Friend / Professional
- Record pet sitter age and phone number
- Update pet sitter data
- Remove a pet sitter (cancels pending invitations)
- View list of all pet sitters with their invitation statuses per cat
- Decline an invitation (public endpoint, no auth required)

---

### Pet Sitter (`ROLE_PET_SITTER`)

A person invited by an owner for a specific cat. Has limited, read-focused access.

**Access**
- Accept an invitation via emailed link (sets password on first access)
- Decline an invitation via emailed link (no account created)
- View the calendar of the cat they are assigned to
- Place chips on the calendar (only chip types allowed by the owner)
- **Cannot** access the owner profile page (`/profile`)
- **Cannot** access pet sitter management (`/pet-sitters`)
- **Cannot** manage cats, chip types, or other owners' calendars

---

### Admin (`ROLE_ADMIN`) — _future_

Created once via a one-shot console command on project installation.

**Planned capabilities (not yet implemented)**
- View all owners and their cats
- View all pet sitters
- Manage (suspend, delete) owners and pet sitters
- Register new owners (owners do not self-register; admin invites them)

---

## Bounded Contexts

| Context | Responsibility |
|---|---|
| `Identity` | Users, roles, invitations, pet sitters, owner profile |
| `Cat` | Cat aggregate, weight history |
| `Calendar` | Calendar aggregate, chip placement |
| `ChipType` | Chip type catalog (label + color) |
| `Shared` | Abstract exceptions, shared infrastructure (exception middleware) |

---

## Key Constraints

- Pet sitters are scoped to a single owner — they are not global users
- A pet sitter account is only created when they accept the invitation
- Chip types are managed per owner — each owner has their own catalog
- Admin features are intentionally deferred; do not implement until explicitly requested
