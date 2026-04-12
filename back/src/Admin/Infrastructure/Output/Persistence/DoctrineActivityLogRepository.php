<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Output\Persistence;

use App\Admin\Domain\Model\ActivityLog;
use App\Admin\Domain\Model\ActivityLogId;
use App\Admin\Domain\Repository\ActivityLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final readonly class DoctrineActivityLogRepository implements ActivityLogRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(ActivityLog $log): void
    {
        $entity = new ActivityLogOrmEntity();
        $entity->setId($log->id()->value());
        $entity->setMethod($log->method());
        $entity->setPath($log->path());
        $entity->setStatusCode($log->statusCode());
        $entity->setUserId($log->userId());
        $entity->setIp($log->ip());
        $entity->setCreatedAt($log->createdAt());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /** @return list<ActivityLog> */
    public function findPaginated(
        int $page,
        int $limit,
        ?string $userId = null,
        ?string $method = null,
        ?string $search = null,
    ): array {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('l')
            ->from(ActivityLogOrmEntity::class, 'l')
            ->orderBy('l.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $this->applyFilters($qb, $userId, $method, $search);

        /** @var list<ActivityLogOrmEntity> $entities */
        $entities = $qb->getQuery()->getResult();

        return array_map($this->toDomain(...), $entities);
    }

    public function countFiltered(
        ?string $userId = null,
        ?string $method = null,
        ?string $search = null,
    ): int {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(l.id)')
            ->from(ActivityLogOrmEntity::class, 'l');

        $this->applyFilters($qb, $userId, $method, $search);

        /** @var int $count */
        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    private function applyFilters(
        QueryBuilder $qb,
        ?string $userId,
        ?string $method,
        ?string $search,
    ): void {
        if (null !== $userId) {
            $qb->andWhere('l.userId = :userId')->setParameter('userId', $userId);
        }

        if (null !== $method) {
            $qb->andWhere('l.method = :method')->setParameter('method', strtoupper($method));
        }

        if (null !== $search) {
            $qb->andWhere('l.path LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
    }

    private function toDomain(ActivityLogOrmEntity $entity): ActivityLog
    {
        return ActivityLog::reconstitute(
            id: new ActivityLogId($entity->getId()),
            method: $entity->getMethod(),
            path: $entity->getPath(),
            statusCode: $entity->getStatusCode(),
            userId: $entity->getUserId(),
            ip: $entity->getIp(),
            createdAt: $entity->getCreatedAt(),
        );
    }
}
