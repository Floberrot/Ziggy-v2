<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Output\Persistence;

use App\Calendar\Domain\Model\Calendar;
use App\Calendar\Domain\Model\CalendarId;
use App\Calendar\Domain\Model\Chip;
use App\Calendar\Domain\Model\ChipId;
use App\Calendar\Domain\Repository\CalendarRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCalendarRepository implements CalendarRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Calendar $calendar): void
    {
        $entity = $this->entityManager->find(CalendarOrmEntity::class, $calendar->id()->value());

        if (null === $entity) {
            $entity = new CalendarOrmEntity();
            $entity->setId($calendar->id()->value());
            $entity->setCatId($calendar->catId());
            $entity->setCreatedAt($calendar->createdAt());
        }

        $entity->setScheduledChipTypeIds($calendar->scheduledChipTypeIds());

        // Sync chips — add only new ones (chips are append-only for now)
        $existingIds = [];
        foreach ($entity->getChips() as $chipEntity) {
            $existingIds[] = $chipEntity->getId();
        }

        foreach ($calendar->chips() as $chip) {
            if (!in_array($chip->id()->value(), $existingIds, true)) {
                $chipEntity = new ChipOrmEntity();
                $chipEntity->setId($chip->id()->value());
                $chipEntity->setChipTypeId($chip->chipTypeId());
                $chipEntity->setDate($chip->date());
                $chipEntity->setNote($chip->note());
                $chipEntity->setAuthorId($chip->authorId());
                $entity->addChip($chipEntity);
            }
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findById(CalendarId $id): ?Calendar
    {
        $entity = $this->entityManager->find(CalendarOrmEntity::class, $id->value());

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function findByCatId(string $catId): ?Calendar
    {
        $entity = $this->entityManager->getRepository(CalendarOrmEntity::class)
            ->findOneBy(['catId' => $catId]);

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function removeChip(string $chipId): void
    {
        $entity = $this->entityManager->find(ChipOrmEntity::class, $chipId);

        if (null !== $entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }

    private function toDomain(CalendarOrmEntity $entity): Calendar
    {
        $calendar = Calendar::create(
            id: new CalendarId($entity->getId()),
            catId: $entity->getCatId(),
        );

        $chips = array_map(
            static fn (ChipOrmEntity $chipEntity) => new Chip(
                id: new ChipId($chipEntity->getId()),
                chipTypeId: $chipEntity->getChipTypeId(),
                date: $chipEntity->getDate(),
                note: $chipEntity->getNote(),
                authorId: $chipEntity->getAuthorId(),
            ),
            $entity->getChips()->toArray(),
        );

        $calendar->setChips(array_values($chips));
        $calendar->setScheduledChipTypeIds($entity->getScheduledChipTypeIds());

        return $calendar;
    }
}
