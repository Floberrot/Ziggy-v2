<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Input\Console;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ziggy:calendar:backfill-chip-authors',
    description: 'One-shot: sets author_id on chips that have none, using the cat owner as author.',
)]
final class BackfillChipAuthorsCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $affected = $this->connection->executeStatement(
            'UPDATE chips
             SET author_id = cats.owner_id
             FROM calendars, cats
             WHERE chips.calendar_id = calendars.id
               AND calendars.cat_id  = cats.id
               AND chips.author_id   IS NULL',
        );

        $io->success(sprintf('%d chip(s) updated with their cat owner as author.', $affected));

        return Command::SUCCESS;
    }
}
