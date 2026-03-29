<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Output\Persistence;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Invitation;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineInvitationRepository implements InvitationRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Invitation $invitation): void
    {
        $entity = $this->entityManager->find(InvitationOrmEntity::class, $invitation->id());

        if (null === $entity) {
            $entity = new InvitationOrmEntity();
        }

        $entity->setId($invitation->id());
        $entity->setOwnerId($invitation->ownerId()->value());
        $entity->setInviteeEmail($invitation->inviteeEmail()->value());
        $entity->setCatId($invitation->catId());
        $entity->setToken($invitation->token());
        $entity->setExpiresAt($invitation->expiresAt());
        $entity->setAccepted($invitation->isAccepted());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Invitation
    {
        $entity = $this->entityManager->find(InvitationOrmEntity::class, $id);

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function findByToken(string $token): ?Invitation
    {
        $entity = $this->entityManager->getRepository(InvitationOrmEntity::class)
            ->findOneBy(['token' => $token]);

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    /** @return list<Invitation> */
    public function findByOwnerId(string $ownerId): array
    {
        $entities = $this->entityManager->getRepository(InvitationOrmEntity::class)
            ->findBy(['ownerId' => $ownerId], ['id' => 'ASC']);

        return array_map($this->toDomain(...), $entities);
    }

    public function markAccepted(string $token): void
    {
        $entity = $this->entityManager->getRepository(InvitationOrmEntity::class)
            ->findOneBy(['token' => $token]);

        if (null !== $entity) {
            $entity->setAccepted(true);
            $this->entityManager->flush();
        }
    }

    public function remove(string $id): void
    {
        $entity = $this->entityManager->find(InvitationOrmEntity::class, $id);

        if (null !== $entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }

    private function toDomain(InvitationOrmEntity $entity): Invitation
    {
        return Invitation::reconstruct(
            id: $entity->getId(),
            ownerId: new UserId($entity->getOwnerId()),
            inviteeEmail: new Email($entity->getInviteeEmail()),
            catId: $entity->getCatId(),
            token: $entity->getToken(),
            expiresAt: $entity->getExpiresAt(),
            accepted: $entity->isAccepted(),
        );
    }
}
