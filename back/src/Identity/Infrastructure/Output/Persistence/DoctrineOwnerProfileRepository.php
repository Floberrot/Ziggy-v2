<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Output\Persistence;

use App\Identity\Domain\Model\OwnerProfile;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\OwnerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineOwnerProfileRepository implements OwnerProfileRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(OwnerProfile $profile): void
    {
        $entity = $this->entityManager->find(OwnerProfileOrmEntity::class, $profile->userId()->value());

        if (null === $entity) {
            $entity = new OwnerProfileOrmEntity();
        }

        $entity->setUserId($profile->userId()->value());
        $entity->setAge($profile->age());
        $entity->setPhoneNumber($profile->phoneNumber());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findByUserId(UserId $userId): ?OwnerProfile
    {
        $entity = $this->entityManager->find(OwnerProfileOrmEntity::class, $userId->value());

        if (null === $entity) {
            return null;
        }

        return OwnerProfile::reconstruct(
            userId: new UserId($entity->getUserId()),
            age: $entity->getAge(),
            phoneNumber: $entity->getPhoneNumber(),
        );
    }
}
