<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Output\Persistence;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\PetSitter;
use App\Identity\Domain\Model\PetSitterId;
use App\Identity\Domain\Model\PetSitterType;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\PetSitterRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrinePetSitterRepository implements PetSitterRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(PetSitter $petSitter): void
    {
        $entity = $this->entityManager->find(PetSitterOrmEntity::class, $petSitter->id()->value());

        if (null === $entity) {
            $entity = new PetSitterOrmEntity();
        }

        $entity->setId($petSitter->id()->value());
        $entity->setOwnerId($petSitter->ownerId()->value());
        $entity->setInviteeEmail($petSitter->inviteeEmail()->value());
        $entity->setUserId($petSitter->userId()?->value());
        $entity->setType($petSitter->type()->value);
        $entity->setAge($petSitter->age());
        $entity->setPhoneNumber($petSitter->phoneNumber());
        $entity->setCreatedAt($petSitter->createdAt());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findById(PetSitterId $id): ?PetSitter
    {
        $entity = $this->entityManager->find(PetSitterOrmEntity::class, $id->value());

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    /** @return list<PetSitter> */
    public function findByOwnerId(string $ownerId): array
    {
        $entities = $this->entityManager->getRepository(PetSitterOrmEntity::class)
            ->findBy(['ownerId' => $ownerId], ['createdAt' => 'ASC']);

        return array_map($this->toDomain(...), $entities);
    }

    /** @return list<PetSitter> */
    public function findAll(): array
    {
        $entities = $this->entityManager->getRepository(PetSitterOrmEntity::class)->findAll();

        return array_map($this->toDomain(...), $entities);
    }

    /** @return list<PetSitter> */
    public function findAllPaginated(int $page, int $limit): array
    {
        $entities = $this->entityManager->getRepository(PetSitterOrmEntity::class)
            ->findBy([], ['createdAt' => 'DESC'], $limit, ($page - 1) * $limit);

        return array_map($this->toDomain(...), $entities);
    }

    public function countAll(): int
    {
        /** @var int $count */
        $count = $this->entityManager->getRepository(PetSitterOrmEntity::class)->count([]);

        return $count;
    }

    public function findByOwnerAndEmail(string $ownerId, string $inviteeEmail): ?PetSitter
    {
        $entity = $this->entityManager->getRepository(PetSitterOrmEntity::class)
            ->findOneBy(['ownerId' => $ownerId, 'inviteeEmail' => $inviteeEmail]);

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function remove(PetSitterId $id): void
    {
        $entity = $this->entityManager->find(PetSitterOrmEntity::class, $id->value());

        if (null !== $entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }

    private function toDomain(PetSitterOrmEntity $entity): PetSitter
    {
        $userId = $entity->getUserId() !== null ? new UserId($entity->getUserId()) : null;

        return PetSitter::reconstruct(
            id: new PetSitterId($entity->getId()),
            ownerId: new UserId($entity->getOwnerId()),
            inviteeEmail: new Email($entity->getInviteeEmail()),
            createdAt: $entity->getCreatedAt(),
            userId: $userId,
            type: PetSitterType::from($entity->getType()),
            age: $entity->getAge(),
            phoneNumber: $entity->getPhoneNumber(),
        );
    }
}
