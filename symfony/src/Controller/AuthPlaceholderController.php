<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class AuthPlaceholderController
{
    #[Route('/api/person/login', name: 'login_placeholder', methods: ['POST'])]
    #[OA\Post(
        path: '/api/person/login',
        summary: 'Placeholder logowania – zawsze zwraca 401',
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function login(): JsonResponse
    {
        return new JsonResponse(null, 401);
    }
}