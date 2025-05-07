<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AuthPlaceholderController
{
    #[Route('/api/person/login', name: 'login_placeholder', methods: ['POST'])]
    public function login(): JsonResponse
    {
        return new JsonResponse(null, 401);
    }
}
