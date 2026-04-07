<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Output\Persistence;

use App\Admin\Domain\Model\AdminLog;
use App\Admin\Domain\Model\AdminLogId;
use App\Admin\Domain\Repository\AdminLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final readonly class DoctrineAdminLogRepository implements AdminLogRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(AdminLog $log): void
    {
        $entity = $this->entityManager->find(AdminLogOrmEntity::class, $log->id()->value());

        if (null === $entity) {
            $entity = new AdminLogOrmEntity();
        }

        $entity->setId($log->id()->value());
        $entity->setStatusCode($log->statusCode());
        $entity->setMethod($log->method());
        $entity->setPath($log->path());
        $entity->setUserId($log->userId());
        $entity->setMessage($log->message());
        $entity->setStackTrace($log->stackTrace());
        $entity->setLogLevel($log->logLevel());
        $entity->setCreatedAt($log->createdAt());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /** @return list<AdminLog> */
    public function findPaginated(
        int $page,
        int $limit,
        ?string $userId = null,
        ?string $logLevel = null,
        ?string $search = null,
    ): array {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('l')
            ->from(AdminLogOrmEntity::class, 'l')
            ->orderBy('l.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $this->applyFilters($qb, $userId, $logLevel, $search);

        /** @var list<AdminLogOrmEntity> $entities */
        $entities = $qb->getQuery()->getResult();

        return array_map($this->toDomain(...), $entities);
    }

    public function countFiltered(
        ?string $userId = null,
        ?string $logLevel = null,
        ?string $search = null,
    ): int {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(l.id)')
            ->from(AdminLogOrmEntity::class, 'l');

        $this->applyFilters($qb, $userId, $logLevel, $search);

        /** @var int $count */
        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    private function applyFilters(
        QueryBuilder $qb,
        ?string $userId,
        ?string $logLevel,
        ?string $search,
    ): void {
        if (null !== $userId) {
            $qb->andWhere('l.userId = :userId')->setParameter('userId', $userId);
        }

        if (null !== $logLevel) {
            $qb->andWhere('l.logLevel = :logLevel')->setParameter('logLevel', $logLevel);
        }

        if (null !== $search) {
            $qb->andWhere('l.message LIKE :search OR l.path LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
    }

    private function toDomain(AdminLogOrmEntity $entity): AdminLog
    {
        return AdminLog::reconstitute(
            id: new AdminLogId($entity->getId()),
            statusCode: $entity->getStatusCode(),
            method: $entity->getMethod(),
            path: $entity->getPath(),
            userId: $entity->getUserId(),
            message: $entity->getMessage(),
            stackTrace: $entity->getStackTrace(),
            logLevel: $entity->getLogLevel(),
            createdAt: $entity->getCreatedAt(),
        );
    }
}
