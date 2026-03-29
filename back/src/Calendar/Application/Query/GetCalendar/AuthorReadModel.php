<?php

declare(strict_types=1);

namespace App\Calendar\Application\Query\GetCalendar;

interface AuthorReadModel
{
    /**
     * Returns a map of userId => display name (username if set, otherwise email).
     *
     * @param list<string> $userIds
     * @return array<string, string>
     */
    public function findUsernamesByIds(array $userIds): array;
}
