# Backend Architecture — Hexagonal (Ports & Adapters)

The backend follows **Hexagonal Architecture** with three layers: **Domain**, **Application**, and **Infrastructure**. The flow is always: Infrastructure → Application → Domain. Dependencies point inward — Domain knows nothing about Application or Infrastructure.

```
src/
└── <BoundedContext>/
    ├── Domain/
    │   ├── Model/               # Entities, Value Objects, Aggregates
    │   ├── Event/               # Domain Events
    │   ├── Repository/          # Repository interfaces (ports)
    │   ├── Service/             # Domain Services (pure business logic)
    │   └── Exception/           # Domain exceptions
    ├── Application/
    │   ├── Command/             # Commands + Handlers (write side)
    │   ├── Query/               # Queries + Handlers (read side)
    │   └── EventHandler/        # Handles Domain Events via Messenger
    └── Infrastructure/
        ├── Input/               # Controllers, CLI commands, Consumers
        │   ├── Http/            # Symfony controllers (API or Twig)
        │   ├── Console/         # Console commands. Not logic at all. Just dispatch events and return types.
        │   └── Messenger/       # Message consumers (async entry points)
        └── Output/              # Adapters implementing Domain ports
            ├── Persistence/     # Doctrine repositories, read models
            ├── Messaging/       # Event bus, external queues
            └── ExternalService/ # HTTP clients, third-party APIs
```

---

## Domain Layer

The Domain layer contains **all business logic**. It has zero dependencies on Symfony, Doctrine, or any framework.

**Rules:**
- Entities and Aggregates encapsulate state and enforce invariants
- Value Objects are immutable; compare by value, not identity
- Domain Events are raised inside Aggregates when something meaningful happens
- Repository interfaces are defined here as ports — never implemented here
- Domain Services handle logic that doesn't belong to a single entity
- No framework annotations in Domain classes (no `#[ORM\Entity]`, no `#[Route]`)
- Validator before any action if necessary (`UserRegistrationValidator` check email exist)

**Domain Events:**
- Named in past tense: `OrderPlaced`, `UserRegistered`, `PaymentFailed`
- Implement a common `DomainEvent` interface
- Collected on the Aggregate, dispatched by the Application layer after persistence

```php
// Domain/Event/OrderPlaced.php
use DateTimeImmutable;

final readonly class OrderPlaced implements DomainEvent
{
    public function __construct(
        public OrderId $orderId,
        public DateTimeImmutable $occurredAt,
    ) {}
}
```

---

## Application Layer — CQRS with Symfony Messenger

The Application layer orchestrates use cases using **Commands** (write) and **Queries** (read) dispatched through **Symfony Messenger**.

**Rules:**
- One Command = one state change; one Query = one read, no side effects
- Handlers are the only classes allowed to call repositories and dispatch events
- Command Handlers dispatch Domain Events after the aggregate is persisted
- Query Handlers return DTOs or read models — never Domain entities
- No business logic in handlers — delegate everything to the Domain
- No check here. Business logic belong to Domain

**Command (write side):**
```php
// Application/Command/PlaceOrder/PlaceOrderCommand.php
final readonly class PlaceOrderCommand
{
    public function __construct(
        public string $customerId,
        public array $items,
    ) {}
}

// Application/Command/PlaceOrder/PlaceOrderHandler.php
#[AsMessageHandler]
final class PlaceOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private EventBus $eventBus,
    ) {}

    public function __invoke(PlaceOrderCommand $command): void
    {
        $order = Order::place($command->customerId, $command->items);
        $this->orders->save($order);
        $this->eventBus->dispatchAll($order->releaseEvents());
    }
}
```

**Query (read side):**
```php
// Application/Query/GetOrder/GetOrderQuery.php
final readonly class GetOrderQuery
{
    public function __construct(public string $orderId) {}
}

// Application/Query/GetOrder/GetOrderHandler.php
#[AsMessageHandler]
final class GetOrderHandler
{
    public function __invoke(GetOrderQuery $query): OrderView
    {
        return $this->readModel->findById($query->orderId)
            ?? throw new OrderNotFound($query->orderId);
    }
}
```

**Messenger routing (`messenger.yaml`):**
```yaml
framework:
    messenger:
        buses:
            command.bus:
                middleware: [doctrine_transaction]
            query.bus: ~
            event.bus:
                default_middleware: allow_no_handlers
```

---

## Infrastructure Layer

Infrastructure adapters implement Domain ports (Output) and expose entry points (Input).

**Input — driving adapters (they call the application):**
- `Http/` — Symfony controllers dispatch Commands or Queries via Messenger
- `Console/` — Symfony console commands dispatch Commands
- `Messenger/` — async consumers receive external messages and dispatch Commands

```php
// Infrastructure/Input/Http/PlaceOrderController.php
final class PlaceOrderController extends AbstractController
{
    #[Route('/orders', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $this->commandBus->dispatch(new PlaceOrderCommand(
            customerId: $request->get('customer_id'),
            items: $request->get('items'),
        ));

        return $this->json(null, Response::HTTP_ACCEPTED);
    }
}
```
Bad controller example:

```php
    public function __invoke(Request $request): JsonResponse
    {
        /** @var array{token?: string, password?: string} $data */
        $data = json_decode($request->getContent(), true) ?? [];

        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';

        if ('' === $token || '' === $password) {
            return new JsonResponse(['error' => 'Token and password are required.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->commandBus->dispatch(new AcceptInvitationCommand($token, $password));
        } catch (HandlerFailedException $e) {
            $cause = $e->getPrevious();
            if ($cause instanceof \DomainException) {
                return new JsonResponse(['error' => $cause->getMessage()], Response::HTTP_BAD_REQUEST);
            }
            throw $e;
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
```

Good Controller example:
```php
    public function __invoke(Request $request): JsonResponse
    {
        // Retrieve Data using MapRequestPayload to have types object
        $data = $request->typedObject();
        // Check has to be in the MapRequestPayload object returned.

        // No logic here, just dispatch. If an error occured. A middleware catch exception an return response with this error.
        $this->commandBus->dispatch(new AcceptInvitationCommand($token, $password));

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
```
**Output — driven adapters (the application calls them via ports):**
- `Persistence/` — Doctrine `EntityManager`, read model repositories
- `Messaging/` — Messenger event bus implementations, external queue publishers
- `ExternalService/` — HTTP clients, payment gateways, email providers

```php
// Infrastructure/Output/Persistence/DoctrineOrderRepository.php
final class DoctrineOrderRepository implements OrderRepository
{
    public function save(Order $order): void
    {
        $this->em->persist($order);
    }
}
```

---

## Key Rules

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
