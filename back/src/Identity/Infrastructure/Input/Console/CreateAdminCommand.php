<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Console;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Role;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

#[AsCommand(
    name: 'ziggy:create-admin',
    description: 'Create the first admin user (one-shot install command).',
)]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin plain password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $emailArg */
        $emailArg = $input->getArgument('email');
        /** @var string $passwordArg */
        $passwordArg = $input->getArgument('password');

        try {
            $email = new Email($emailArg);
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        if (null !== $this->userRepository->findByEmail($email)) {
            $io->error(sprintf('A user with email "%s" already exists.', $emailArg));

            return Command::FAILURE;
        }

        $userId = UserId::generate();

        $hashedPassword = $this->passwordHasher->hashPassword(
            new InMemoryUser($emailArg, ''),
            $passwordArg,
        );

        $admin = User::register(
            id: $userId,
            email: $email,
            hashedPassword: $hashedPassword,
            role: Role::ADMIN,
        );

        $this->userRepository->save($admin);

        $io->success(sprintf('Admin user "%s" created successfully.', $emailArg));

        return Command::SUCCESS;
    }
}
