<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListActivityLogs;

final readonly class ActivityLogView
{
    public function __construct(
        public string $id,
        public string $method,
        public string $path,
        public int $statusCode,
        public ?string $userId,
        public ?string $ip,
        public string $createdAt,
    ) {
    }

    /**
     * @return array{
     *     id: string, method: string, path: string, statusCode: int,
     *     userId: string|null, ip: string|null, createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'path' => $this->path,
            'statusCode' => $this->statusCode,
            'userId' => $this->userId,
            'ip' => $this->ip,
            'createdAt' => $this->createdAt,
        ];
    }
}
