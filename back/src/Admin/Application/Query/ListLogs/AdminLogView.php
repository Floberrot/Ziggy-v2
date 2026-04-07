<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListLogs;

final readonly class AdminLogView
{
    public function __construct(
        public string $id,
        public int $statusCode,
        public string $method,
        public string $path,
        public ?string $userId,
        public string $message,
        public ?string $stackTrace,
        public string $logLevel,
        public string $createdAt,
    ) {
    }

    /**
     * @return array{
     *     id: string, statusCode: int, method: string, path: string,
     *     userId: string|null, message: string, stackTrace: string|null,
     *     logLevel: string, createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'statusCode' => $this->statusCode,
            'method' => $this->method,
            'path' => $this->path,
            'userId' => $this->userId,
            'message' => $this->message,
            'stackTrace' => $this->stackTrace,
            'logLevel' => $this->logLevel,
            'createdAt' => $this->createdAt,
        ];
    }
}
