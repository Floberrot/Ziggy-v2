<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http;

use App\Admin\Infrastructure\Output\FileSystem\LogFileReader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/logs/server', methods: ['GET'])]
final readonly class ReadServerLogsController
{
    private const string CADDY_LOG_PATH = '/var/log/caddy/access.log';

    public function __construct(private LogFileReader $logFileReader)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $lines = min(500, max(1, (int) $request->query->get('lines', 200)));

        return new JsonResponse([
            'lines' => $this->logFileReader->tail(self::CADDY_LOG_PATH, $lines),
            'file' => 'access.log',
        ]);
    }
}
