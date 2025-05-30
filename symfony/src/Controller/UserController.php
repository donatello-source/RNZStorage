<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Person;
use OpenApi\Attributes as OA;

class UserController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz dane zalogowanego użytkownika',
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dane użytkownika',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'imie', type: 'string', example: 'Jan'),
                        new OA\Property(property: 'nazwisko', type: 'string', example: 'Kowalski'),
                        new OA\Property(property: 'mail', type: 'string', example: 'jan.kowalski@example.com'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'))
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Not authenticated')
                    ]
                )
            )
        ]
    )]
    public function me(): JsonResponse
    {
        /** @var Person $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'imie' => $user->getImie(),
            'nazwisko' => $user->getNazwisko(),
            'mail' => $user->getMail(),
            'roles' => $user->getRoles(),
        ]);
    }
}
