<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http;

use App\Admin\Infrastructure\Output\FileSystem\LogFileReader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/logs/app', methods: ['GET'])]
final readonly class ReadAppLogsController
{
    public function __construct(
        private LogFileReader $logFileReader,
        #[Autowire('%kernel.logs_dir%/app.log')]
        private string $logFilePath,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $lines = min(500, max(1, (int) $request->query->get('lines', 200)));

        return new JsonResponse([
            'lines' => $this->logFileReader->tail($this->logFilePath, $lines),
            'file' => 'app.log',
        ]);
    }
}
