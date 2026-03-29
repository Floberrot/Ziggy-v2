<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Output\Persistence;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\PasswordResetToken;
use App\Identity\Domain\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrinePasswordResetTokenRepository implements PasswordResetTokenRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(PasswordResetToken $token): void
    {
        $entity = $this->entityManager->find(PasswordResetTokenOrmEntity::class, $token->id());

        if (null === $entity) {
            $entity = new PasswordResetTokenOrmEntity();
        }

        $entity->setId($token->id());
        $entity->setEmail($token->email()->value());
        $entity->setToken($token->token());
        $entity->setExpiresAt($token->expiresAt());
        $entity->setUsed($token->isUsed());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findByToken(string $token): ?PasswordResetToken
    {
        $entity = $this->entityManager->getRepository(PasswordResetTokenOrmEntity::class)
            ->findOneBy(['token' => $token]);

        if (null === $entity) {
            return null;
        }

        return PasswordResetToken::reconstruct(
            id: $entity->getId(),
            email: new Email($entity->getEmail()),
            token: $entity->getToken(),
            expiresAt: $entity->getExpiresAt(),
            used: $entity->isUsed(),
        );
    }

    public function markUsed(string $token): void
    {
        $entity = $this->entityManager->getRepository(PasswordResetTokenOrmEntity::class)
            ->findOneBy(['token' => $token]);

        if (null !== $entity) {
            $entity->setUsed(true);
            $this->entityManager->flush();
        }
    }
}
