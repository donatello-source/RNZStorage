<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSuccessListener
{
    public function onLogoutSuccess(LogoutEvent $event): void
    {
        $response = new JsonResponse([
            'message' => 'Wylogowano pomyślnie'
        ]);

        $event->setResponse($response);
    }
}
