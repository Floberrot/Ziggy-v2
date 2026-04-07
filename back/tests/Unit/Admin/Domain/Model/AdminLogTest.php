<?php

declare(strict_types=1);

namespace App\Tests\Unit\Admin\Domain\Model;

use App\Admin\Domain\Model\AdminLog;
use App\Admin\Domain\Model\AdminLogId;
use PHPUnit\Framework\TestCase;

final class AdminLogTest extends TestCase
{
    public function testRecordSetsCorrectLogLevelForServerError(): void
    {
        $log = AdminLog::record(
            id: AdminLogId::generate(),
            statusCode: 500,
            method: 'GET',
            path: '/api/cats',
            userId: null,
            message: 'An unexpected error occurred.',
            stackTrace: '#0 ...',
        );

        self::assertSame('error', $log->logLevel());
        self::assertSame(500, $log->statusCode());
    }

    public function testRecordSetsWarningLevelForClientError(): void
    {
        $log = AdminLog::record(
            id: AdminLogId::generate(),
            statusCode: 404,
            method: 'GET',
            path: '/api/cats/unknown',
            userId: 'user-uuid',
            message: 'Cat not found.',
            stackTrace: null,
        );

        self::assertSame('warning', $log->logLevel());
        self::assertSame(404, $log->statusCode());
        self::assertSame('user-uuid', $log->userId());
        self::assertNull($log->stackTrace());
    }

    public function testRecordStoresAllFields(): void
    {
        $id = AdminLogId::generate();
        $log = AdminLog::record(
            id: $id,
            statusCode: 400,
            method: 'POST',
            path: '/api/cats',
            userId: null,
            message: 'Validation failed.',
            stackTrace: null,
        );

        self::assertSame($id->value(), $log->id()->value());
        self::assertSame('POST', $log->method());
        self::assertSame('/api/cats', $log->path());
        self::assertSame('Validation failed.', $log->message());
    }

    public function testReconstitutePreservesAllData(): void
    {
        $id = AdminLogId::generate();
        $createdAt = new \DateTimeImmutable('2026-01-01 12:00:00');

        $log = AdminLog::reconstitute(
            id: $id,
            statusCode: 500,
            method: 'DELETE',
            path: '/api/admin/users/123',
            userId: 'admin@ziggy.dev',
            message: 'Server error.',
            stackTrace: '#0 ...',
            logLevel: 'error',
            createdAt: $createdAt,
        );

        self::assertSame($id->value(), $log->id()->value());
        self::assertSame(500, $log->statusCode());
        self::assertSame('DELETE', $log->method());
        self::assertSame('admin@ziggy.dev', $log->userId());
        self::assertSame('error', $log->logLevel());
        self::assertSame($createdAt, $log->createdAt());
    }
}
