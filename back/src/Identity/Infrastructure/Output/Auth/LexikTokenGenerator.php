<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Output\Auth;

use App\Identity\Application\Port\TokenGenerator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final readonly class LexikTokenGenerator implements TokenGenerator
{
    /**
     * @param UserProviderInterface<UserInterface> $userProvider
     */
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private UserProviderInterface $userProvider,
    ) {
    }

    public function generateForEmail(string $email): string
    {
        $user = $this->userProvider->loadUserByIdentifier($email);

        return $this->jwtManager->create($user);
    }
}
