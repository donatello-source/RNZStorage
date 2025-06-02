<?php

namespace App\Controller;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PersonService;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[Route('/api/person')]
final class PersonController extends AbstractController
{
    public function __construct(private readonly PersonService $personService) {}
    #[Route('', name: 'person_all', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz wszystkie osoby',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista osób',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'imie', type: 'string', example: 'Jan'),
                            new OA\Property(property: 'nazwisko', type: 'string', example: 'Kowalski'),
                            new OA\Property(property: 'mail', type: 'string', example: 'jan.kowalski@example.com'),
                            new OA\Property(property: 'stanowisko', type: 'string', example: 'Administrator')
                        ]
                    )
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Osoby')]
    public function personAll(PersonService $personService): JsonResponse
    {
        return $this->json($personService->getAll(), 200);
    }

    #[Route('/{id}', name: 'get_person_by_id', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz osobę po ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Id Osoby',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'imie', type: 'string', example: 'Jan'),
                            new OA\Property(property: 'nazwisko', type: 'string', example: 'Kowalski'),
                            new OA\Property(property: 'mail', type: 'string', example: 'jan.kowalski@example.com'),
                            new OA\Property(property: 'stanowisko', type: 'string', example: 'Administrator')
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Osoba nie znaleziona',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Osoba nie znaleziona')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Brak autoryzacji'
            )
        ]
    )]
    #[OA\Tag(name: 'Osoby')]
    public function getPersonById(int $id): JsonResponse
    {
        $person = $this->personService->getById($id);
    
        if (!$person) {
            throw new NotFoundHttpException('Osoba nie znaleziona');
        }
    
        return $this->json($person);
    }
    

    #[Route('', name: 'add_person', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaj nową osobę',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['imie', 'nazwisko', 'mail', 'haslo'],
                properties: [
                    new OA\Property(property: 'imie', type: 'string', example: 'Anna'),
                    new OA\Property(property: 'nazwisko', type: 'string', example: 'Nowak'),
                    new OA\Property(property: 'mail', type: 'string', example: 'anna.nowak@example.com'),
                    new OA\Property(property: 'haslo', type: 'string', example: 'superhaslo123')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Dodano osobę'),
            new OA\Response(
                response: 400,
                description: 'Brak wymaganych pól',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Brak wymaganych pól')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    #[OA\Tag(name: 'Osoby')]
    public function addPerson(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $person = $this->personService->create($data);
    
        return $this->json(['message' => 'Dodano nową osobę', 'data' => $person], 201);
    }
    
}
