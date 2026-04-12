<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Http;

use App\Admin\Domain\Model\ActivityLog;
use App\Admin\Domain\Model\ActivityLogId;
use App\Admin\Domain\Repository\ActivityLogRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final readonly class RequestActivityListener implements EventSubscriberInterface
{
    public function __construct(private ActivityLogRepository $activityLogRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::TERMINATE => 'onKernelTerminate'];
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        // Skip CORS preflight requests
        if ('OPTIONS' === $request->getMethod()) {
            return;
        }

        $userId = null;
        $userIdentifier = $request->attributes->get('_security_user');
        if (\is_string($userIdentifier)) {
            $userId = $userIdentifier;
        }

        try {
            $this->activityLogRepository->save(ActivityLog::record(
                id: ActivityLogId::generate(),
                method: $request->getMethod(),
                path: $request->getPathInfo(),
                statusCode: $response->getStatusCode(),
                userId: $userId,
                ip: $request->getClientIp(),
            ));
        } catch (Throwable) {
            // Never let activity logging crash the application
        }
    }
}
