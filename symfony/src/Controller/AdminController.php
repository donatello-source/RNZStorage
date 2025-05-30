<?php

namespace App\Controller;

use App\Repository\PersonRepository;
use App\Entity\Person;
use App\Service\PersonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

class AdminController
{
    #[Route('/api/admin/users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/users',
        summary: 'Lista wszystkich użytkowników (tylko admin)',
        tags: ['Admin'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista użytkowników',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'imie', type: 'string'),
                                    new OA\Property(property: 'nazwisko', type: 'string'),
                                    new OA\Property(property: 'mail', type: 'string'),
                                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'))
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Brak autoryzacji'),
            new OA\Response(response: 403, description: 'Brak uprawnień')
        ]
    )]
    public function listUsers(PersonRepository $personRepository): JsonResponse
    {
        $users = $personRepository->findAll();
        $data = array_map(fn($u) => [
            'id' => $u->getId(),
            'imie' => $u->getImie(),
            'nazwisko' => $u->getNazwisko(),
            'mail' => $u->getMail(),
            'roles' => $u->getRoles()
        ], $users);

        return new JsonResponse(['data' => $data]);
    }

    #[Route('/api/admin/users/{id}/roles', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        path: '/api/admin/users/{id}/roles',
        summary: 'Aktualizacja ról użytkownika (tylko admin)',
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                description: 'ID użytkownika'
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'roles', type: 'string', example: 'ROLE_ADMIN')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Role zaktualizowane',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Role zaktualizowane')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Nieprawidłowa rola',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Nieprawidłowa rola')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Użytkownik nie znaleziony',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Użytkownik nie znaleziony')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Brak autoryzacji'),
            new OA\Response(response: 403, description: 'Brak uprawnień')
        ]
    )]
    public function updateUserRoles(int $id, Request $request, EntityManagerInterface $em, PersonRepository $repo): JsonResponse
    {
        $user = $repo->find($id);
        if (!$user) return new JsonResponse(['error' => 'Użytkownik nie znaleziony'], 404);

        $content = json_decode($request->getContent(), true);
        if (!isset($content['roles']) || !is_string($content['roles'])) {
            return new JsonResponse(['error' => 'Nieprawidłowa rola'], 400);
        }
        
        $user->setRoles([$content['roles']]);
        $em->flush();

        return new JsonResponse(['message' => 'Role zaktualizowane']);
    }
}
