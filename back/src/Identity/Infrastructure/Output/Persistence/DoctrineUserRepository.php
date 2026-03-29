<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Output\Persistence;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Role;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(User $user): void
    {
        $entity = $this->entityManager->find(UserOrmEntity::class, $user->id()->value());

        if (null === $entity) {
            $entity = new UserOrmEntity();
        }

        $entity->setId($user->id()->value());
        $entity->setEmail($user->email()->value());
        $entity->setPassword($user->hashedPassword());
        $entity->setRole($user->role()->value);
        $entity->setUsername($user->username());
        $entity->setCreatedAt($user->createdAt());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findById(UserId $id): ?User
    {
        $entity = $this->entityManager->find(UserOrmEntity::class, $id->value());

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function findByEmail(Email $email): ?User
    {
        $entity = $this->entityManager->getRepository(UserOrmEntity::class)
            ->findOneBy(['email' => $email->value()]);

        if (null === $entity) {
            return null;
        }

        return $this->toDomain($entity);
    }

    /** @return list<User> */
    public function findAll(): array
    {
        $entities = $this->entityManager->getRepository(UserOrmEntity::class)->findAll();

        return array_map($this->toDomain(...), $entities);
    }

    private function toDomain(UserOrmEntity $entity): User
    {
        return User::register(
            id: new UserId($entity->getId()),
            email: new Email($entity->getEmail()),
            hashedPassword: $entity->getPassword(),
            role: Role::from($entity->getRole()),
            username: $entity->getUsername(),
        );
    }
}
