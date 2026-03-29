<?php

declare(strict_types=1);

namespace App\ChipType\Infrastructure\Output\Persistence;

use App\ChipType\Domain\Model\ChipColor;
use App\ChipType\Domain\Model\ChipType;
use App\ChipType\Domain\Model\ChipTypeId;
use App\ChipType\Domain\Repository\ChipTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineChipTypeRepository implements ChipTypeRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(ChipType $chipType): void
    {
        $entity = $this->entityManager->find(ChipTypeOrmEntity::class, $chipType->id()->value());

        if (null === $entity) {
            $entity = new ChipTypeOrmEntity();
        }

        $entity->setId($chipType->id()->value());
        $entity->setName($chipType->name());
        $entity->setColor($chipType->color()->value());
        $entity->setOwnerId($chipType->ownerId());
        $entity->setCreatedAt($chipType->createdAt());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findById(ChipTypeId $id): ?ChipType
    {
        $entity = $this->entityManager->find(ChipTypeOrmEntity::class, $id->value());

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    /** @return list<ChipType> */
    public function findByOwnerId(string $ownerId): array
    {
        $entities = $this->entityManager->getRepository(ChipTypeOrmEntity::class)
            ->findBy(['ownerId' => $ownerId]);

        return array_map($this->toDomain(...), $entities);
    }

    public function remove(ChipTypeId $id): void
    {
        $entity = $this->entityManager->find(ChipTypeOrmEntity::class, $id->value());

        if (null !== $entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }

    private function toDomain(ChipTypeOrmEntity $entity): ChipType
    {
        return ChipType::create(
            id: new ChipTypeId($entity->getId()),
            name: $entity->getName(),
            color: new ChipColor($entity->getColor()),
            ownerId: $entity->getOwnerId(),
        );
    }
}
