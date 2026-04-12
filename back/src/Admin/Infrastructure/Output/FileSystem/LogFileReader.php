<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Output\FileSystem;

final readonly class LogFileReader
{
    private const int MAX_LINES = 500;
    private const int MAX_BYTES = 512_000;

    /**
     * Returns the last $lines lines of a log file.
     *
     * @return list<string>
     */
    public function tail(string $filePath, int $lines): array
    {
        $lines = min($lines, self::MAX_LINES);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        $fileSize = filesize($filePath);
        if (false === $fileSize || 0 === $fileSize) {
            return [];
        }

        $handle = fopen($filePath, 'r');
        if (false === $handle) {
            return [];
        }

        $bytesToRead = min($fileSize, self::MAX_BYTES);
        fseek($handle, -$bytesToRead, SEEK_END);

        $content = fread($handle, $bytesToRead);
        fclose($handle);

        if (false === $content) {
            return [];
        }

        /** @var list<string> $allLines */
        $allLines = explode("\n", $content);

        // If we didn't start from the beginning, discard the potentially incomplete first line
        if ($bytesToRead < $fileSize) {
            array_shift($allLines);
        }

        // Remove trailing empty line
        $lastKey = array_key_last($allLines);
        if (null !== $lastKey && '' === $allLines[$lastKey]) {
            array_pop($allLines);
        }

        /** @var list<string> $result */
        $result = array_values(array_slice($allLines, -$lines));

        return $result;
    }
}
