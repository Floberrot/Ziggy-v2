<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListLogs;

final readonly class ListLogsQuery
{
    public function __construct(
        public int $page = 1,
        public int $limit = 50,
        public ?string $userId = null,
        public ?string $logLevel = null,
        public ?string $search = null,
    ) {
    }
}
