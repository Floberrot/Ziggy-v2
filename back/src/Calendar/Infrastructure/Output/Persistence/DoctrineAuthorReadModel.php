<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Output\Persistence;

use App\Calendar\Application\Query\GetCalendar\AuthorReadModel;
use App\Identity\Infrastructure\Output\Persistence\UserOrmEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAuthorReadModel implements AuthorReadModel
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param list<string> $userIds
     * @return array<string, string>
     */
    public function findUsernamesByIds(array $userIds): array
    {
        if ([] === $userIds) {
            return [];
        }

        /** @var array<int, array{id: string, username: string|null, email: string}> $rows */
        $rows = $this->entityManager->createQueryBuilder()
            ->select('u.id, u.username, u.email')
            ->from(UserOrmEntity::class, 'u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $userIds)
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['id']] = $row['username'] ?? $row['email'];
        }

        return $map;
    }
}
