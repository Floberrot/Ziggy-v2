<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListCats;

use App\Cat\Domain\Repository\CatRepository;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Repository\UserRepository;
use App\Shared\Application\DTO\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListCatsAdminHandler
{
    public function __construct(
        private CatRepository $catRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(ListCatsAdminQuery $query): PaginatedResult
    {
        $cats = $this->catRepository->findAllPaginated($query->page, $query->limit);
        $total = $this->catRepository->countAll();

        // Batch-load owners by unique emails to avoid N+1
        $ownerEmails = array_unique(array_map(static fn ($cat) => $cat->ownerId(), $cats));
        $ownersByEmail = [];
        foreach ($ownerEmails as $email) {
            $owner = $this->userRepository->findByEmail(new Email($email));
            if (null !== $owner) {
                $ownersByEmail[$email] = $owner;
            }
        }

        $items = array_map(static function ($cat) use ($ownersByEmail) {
            $owner = $ownersByEmail[$cat->ownerId()] ?? null;

            return (new CatAdminView(
                id: $cat->id()->value(),
                name: $cat->name()->value(),
                weight: $cat->weight(),
                breed: $cat->breed(),
                colors: $cat->colors(),
                ownerId: $cat->ownerId(),
                ownerEmail: $owner?->email()->value(),
                ownerUsername: $owner?->username(),
                createdAt: $cat->createdAt()->format(\DateTimeInterface::ATOM),
            ))->toArray();
        }, $cats);

        return new PaginatedResult(
            items: $items,
            total: $total,
            page: $query->page,
            limit: $query->limit,
        );
    }
}
