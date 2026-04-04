<?php

declare(strict_types=1);

namespace App\Cat\Infrastructure\Output\Persistence;

use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Model\CatWeightEntry;
use App\Cat\Domain\Model\CatWeightEntryId;
use App\Cat\Domain\Repository\CatWeightRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCatWeightRepository implements CatWeightRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(CatWeightEntry $entry): void
    {
        $entity = new CatWeightEntryOrmEntity();
        $entity->setId($entry->id->value());
        $entity->setCatId($entry->catId->value());
        $entity->setWeight($entry->weight);
        $entity->setRecordedAt($entry->recordedAt);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /** @return list<CatWeightEntry> */
    public function findByCatId(CatId $catId): array
    {
        /** @var list<CatWeightEntryOrmEntity> $entities */
        $entities = $this->entityManager->getRepository(CatWeightEntryOrmEntity::class)
            ->findBy(['catId' => $catId->value()], ['recordedAt' => 'ASC']);

        return array_map(
            static fn (CatWeightEntryOrmEntity $e) => new CatWeightEntry(
                id: new CatWeightEntryId($e->getId()),
                catId: new CatId($e->getCatId()),
                weight: $e->getWeight(),
                recordedAt: $e->getRecordedAt(),
            ),
            $entities,
        );
    }
}
