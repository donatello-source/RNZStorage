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
            )
        ]
    )]
    #[OA\Tag(name: 'Osoby')]
    public function getPersonById(int $id, PersonService $personService): JsonResponse
    {
        $person = $personService->getById($id);
        if (!$person) {
            return $this->json(['error' => 'Person not found'], 404);
        }
        return $this->json($person, 200);
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
            new OA\Response(response: 400, description: 'Brak wymaganych pól')
        ]
    )]
    #[OA\Tag(name: 'Osoby')]
    public function addPerson(Request $request, PersonService $personService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $person = $personService->create($data);
    
        if (!$person) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }
    
        return $this->json(['message' => 'Dodano nową osobę', 'data' => $person], 201);
    }
    
    #[Route('/login', name: 'login_person', methods: ['POST'])]
    #[OA\Post(
        summary: 'Logowanie osoby',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['mail', 'haslo'],
                properties: [
                    new OA\Property(property: 'mail', type: 'string', example: 'anna.nowak@example.com'),
                    new OA\Property(property: 'haslo', type: 'string', example: 'superhaslo123')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Zalogowano poprawnie'),
            new OA\Response(response: 400, description: 'Brak wymaganych danych'),
            new OA\Response(response: 401, description: 'Nieprawidłowy email lub hasło')
        ]
    )]
    #[OA\Tag(name: 'Osoby')]
    public function loginPerson(Request $request, PersonRepository $personRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['mail'], $data['haslo'])) {
            return $this->json(['error' => 'Missing email or password'], 400);
        }

        $person = $personRepository->findOneBy(['mail' => $data['mail']]);

        if (!$person || !password_verify($data['haslo'], $person->getHaslo())) {
            return $this->json(['error' => 'Invalid email or password'], 401);
        }

        // TODO: JWT Token logic

        return $this->json(['message' => 'Logged in successfully', 'data' => $person], 200);
    }
}
