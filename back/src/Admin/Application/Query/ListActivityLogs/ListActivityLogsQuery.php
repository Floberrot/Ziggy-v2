<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListActivityLogs;

final readonly class ListActivityLogsQuery
{
    public function __construct(
        public int $page = 1,
        public int $limit = 50,
        public ?string $userId = null,
        public ?string $method = null,
        public ?string $search = null,
    ) {
    }
}
