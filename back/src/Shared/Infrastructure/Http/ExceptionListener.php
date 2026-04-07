<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Domain\Exception\ConflictException;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\Port\HttpErrorLogger;
use DomainException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final readonly class ExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private HttpErrorLogger $httpErrorLogger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        if ($exception instanceof HandlerFailedException && $exception->getPrevious() !== null) {
            $exception = $exception->getPrevious();
        }

        $this->logger->error($exception->getMessage(), ['exception' => $exception]);

        [$status, $message] = match (true) {
            $exception instanceof NotFoundException => [Response::HTTP_NOT_FOUND, $exception->getMessage()],
            $exception instanceof ConflictException => [Response::HTTP_CONFLICT, $exception->getMessage()],
            $exception instanceof DomainException => [Response::HTTP_BAD_REQUEST, $exception->getMessage()],
            $exception instanceof InvalidArgumentException => [Response::HTTP_BAD_REQUEST, $exception->getMessage()],
            default => [Response::HTTP_INTERNAL_SERVER_ERROR, 'An unexpected error occurred.'],
        };

        $request = $event->getRequest();
        $userId = $request->attributes->get('_user_id');

        if (null === $userId) {
            $userIdentifier = $request->attributes->get('_security_user');
            if (\is_string($userIdentifier)) {
                $userId = $userIdentifier;
            }
        }

        try {
            $this->httpErrorLogger->log(
                statusCode: $status,
                method: $request->getMethod(),
                path: $request->getPathInfo(),
                userId: \is_string($userId) ? $userId : null,
                message: $exception->getMessage(),
                exception: $exception,
            );
        } catch (\Throwable) {
            // Never let log persistence crash the response
        }

        $event->setResponse(new JsonResponse(['error' => $message], $status));
    }
}
