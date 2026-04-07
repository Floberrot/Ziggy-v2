<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http;

use App\Admin\Infrastructure\Input\Http\Request\AdminLoginRequest;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Role;
use App\Identity\Domain\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\InMemoryUser;

#[Route('/api/admin/auth/login', methods: ['POST'])]
final readonly class AdminLoginController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        #[Autowire(env: 'ADMIN_SECRET')]
        private string $adminSecret,
    ) {
    }

    public function __invoke(#[MapRequestPayload] AdminLoginRequest $request): JsonResponse
    {
        if ($request->adminSecret !== $this->adminSecret) {
            return new JsonResponse(['error' => 'Invalid admin credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->findByEmail(new Email($request->email));

        if (null === $user) {
            throw new UserNotFoundException($request->email);
        }

        if ($user->role() !== Role::ADMIN) {
            return new JsonResponse(['error' => 'Access denied. Admin role required.'], Response::HTTP_FORBIDDEN);
        }

        // Pass the stored hash so isPasswordValid can verify the plain password against it
        $securityUser = new InMemoryUser($request->email, $user->hashedPassword(), [$user->role()->value, 'ROLE_USER']);

        if (!$this->passwordHasher->isPasswordValid($securityUser, $request->password)) {
            return new JsonResponse(['error' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtManager->createFromPayload(
            $securityUser,
            [
                'email' => $request->email,
                'roles' => [$user->role()->value, 'ROLE_USER'],
                'userId' => $user->id()->value(),
            ],
        );

        return new JsonResponse(['token' => $token]);
    }
}
