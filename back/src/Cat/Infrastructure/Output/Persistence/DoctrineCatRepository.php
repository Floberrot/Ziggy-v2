<?php

declare(strict_types=1);

namespace App\Cat\Infrastructure\Output\Persistence;

use App\Cat\Domain\Model\Cat;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Model\CatName;
use App\Cat\Domain\Repository\CatRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCatRepository implements CatRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Cat $cat): void
    {
        $entity = $this->entityManager->find(CatOrmEntity::class, $cat->id()->value());

        if (null === $entity) {
            $entity = new CatOrmEntity();
        }

        $entity->setId($cat->id()->value());
        $entity->setName($cat->name()->value());
        $entity->setWeight($cat->weight());
        $entity->setBreed($cat->breed());
        $entity->setColors($cat->colors());
        $entity->setOwnerId($cat->ownerId());
        $entity->setCreatedAt($cat->createdAt());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findById(CatId $id): ?Cat
    {
        $entity = $this->entityManager->find(CatOrmEntity::class, $id->value());

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    /** @return list<Cat> */
    public function findByOwnerId(string $ownerId): array
    {
        $entities = $this->entityManager->getRepository(CatOrmEntity::class)
            ->findBy(['ownerId' => $ownerId]);

        return array_map($this->toDomain(...), $entities);
    }

    /** @return list<Cat> */
    public function findAll(): array
    {
        $entities = $this->entityManager->getRepository(CatOrmEntity::class)->findAll();

        return array_map($this->toDomain(...), $entities);
    }

    /** @return list<Cat> */
    public function findAllPaginated(int $page, int $limit): array
    {
        $entities = $this->entityManager->getRepository(CatOrmEntity::class)
            ->findBy([], ['createdAt' => 'DESC'], $limit, ($page - 1) * $limit);

        return array_map($this->toDomain(...), $entities);
    }

    public function countAll(): int
    {
        /** @var int $count */
        $count = $this->entityManager->getRepository(CatOrmEntity::class)->count([]);

        return $count;
    }

    public function remove(CatId $id): void
    {
        $entity = $this->entityManager->find(CatOrmEntity::class, $id->value());

        if (null !== $entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }

    private function toDomain(CatOrmEntity $entity): Cat
    {
        return Cat::add(
            id: new CatId($entity->getId()),
            name: new CatName($entity->getName()),
            ownerId: $entity->getOwnerId(),
            weight: $entity->getWeight(),
            breed: $entity->getBreed(),
            colors: $entity->getColors(),
        );
    }
}
