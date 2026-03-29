<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Console;

use App\Identity\Application\Command\UpdateUsername\UpdateUsernameCommand as UpdateUsernameAppCommand;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'ziggy:update-username', description: 'Update username for an existing user')]
final class UpdateUsernameConsoleCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MessageBusInterface $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('username', InputArgument::REQUIRED, 'New username');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $email */
        $email = $input->getArgument('email');
        /** @var string $username */
        $username = $input->getArgument('username');

        $user = $this->userRepository->findByEmail(new Email($email));

        if (null === $user) {
            $io->error(sprintf('No user found with email "%s".', $email));
            return Command::FAILURE;
        }

        $this->commandBus->dispatch(new UpdateUsernameAppCommand(
            userId: $user->id()->value(),
            username: $username,
        ));

        $io->success(sprintf('Username updated to "%s" for %s.', $username, $email));
        return Command::SUCCESS;
    }
}
