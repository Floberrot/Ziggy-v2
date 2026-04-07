<?php

declare(strict_types=1);

namespace App\Shared\Application\DTO;

final readonly class PaginatedResult
{
    /** @param list<mixed> $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function totalPages(): int
    {
        if (0 === $this->limit) {
            return 0;
        }

        return (int) ceil($this->total / $this->limit);
    }

    /** @return array{items: list<mixed>, total: int, page: int, limit: int, totalPages: int} */
    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
            'totalPages' => $this->totalPages(),
        ];
    }
}
