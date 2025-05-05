<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Psr\Log\LoggerInterface;

class ExceptionListener
{
    public function __construct(private LoggerInterface $logger) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        if ($statusCode >= 500) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }

        $response = new JsonResponse([
            'error' => $exception->getMessage(),
            'status' => $statusCode,
        ], $statusCode);

        $event->setResponse($response);
    }
}
