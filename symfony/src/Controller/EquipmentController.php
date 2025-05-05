<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EquipmentService;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Route('/api/equipment')]
final class EquipmentController extends AbstractController
{
    public function __construct(private readonly EquipmentService $equipmentService) {}
    #[Route('', name: 'equipment_all', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobiera listę całego sprzętu',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista sprzętu',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Laptop Dell'),
                            new OA\Property(property: 'description', type: 'string', example: 'Laptop służbowy'),
                            new OA\Property(property: 'quantity', type: 'integer', example: 5),
                            new OA\Property(property: 'price', type: 'number', format: 'float', example: 3999.99),
                            new OA\Property(property: 'categoryid', type: 'integer', example: 2),
                            new OA\Property(property: 'category', type: 'string', example: 'Laptopy')
                        ]
                    )
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Sprzęt')]
    public function equipmentAll(): JsonResponse
    {
        return $this->json($this->equipmentService->getAll());
    }

    #[Route('/{id}', name: 'get_equipment_by_id', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobiera sprzęt po ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID sprzętu',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca dane sprzętu',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Laptop Dell'),
                        new OA\Property(property: 'description', type: 'string', example: 'Laptop służbowy'),
                        new OA\Property(property: 'quantity', type: 'integer', example: 5),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 3999.99),
                        new OA\Property(property: 'categoryid', type: 'integer', example: 2),
                        new OA\Property(property: 'category', type: 'string', example: 'Laptopy')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Sprzęt nie znaleziony'
            )
        ]
    )]
    #[OA\Tag(name: 'Sprzęt')]
    public function getEquipmentById(int $id): JsonResponse
    {
        $equipment = $this->equipmentService->getById($id);
        if (!$equipment) {
            throw new NotFoundHttpException('Sprzęt nie znaleziony');
        }
        return $this->json($equipment);
    }
    



    #[Route('/category/{categoryId}', name: 'get_equipment_by_category', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobiera sprzęt z danej kategorii po ID',
        parameters: [
            new OA\Parameter(name: 'categoryId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista sprzętu z danej kategorii',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Laptop Dell'),
                            new OA\Property(property: 'description', type: 'string', example: 'Laptop służbowy'),
                            new OA\Property(property: 'quantity', type: 'integer', example: 5),
                            new OA\Property(property: 'price', type: 'number', format: 'float', example: 3999.99),
                            new OA\Property(property: 'categoryid', type: 'integer', example: 2),
                            new OA\Property(property: 'category', type: 'string', example: 'Laptopy')
                        ]
                    )
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Sprzęt')]
    public function getEquipmentByCategory(int $categoryId): JsonResponse
    {
        $equipment = $this->equipmentService->getByCategory($categoryId);
        if (!$equipment) {
            throw new NotFoundHttpException('Kategoria nie znaleziona');
        }
        return $this->json($equipment);
    }
    
    

    #[Route('', name: 'add_equipment', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaje nowy sprzęt',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'quantity', 'price'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Monitor Samsung'),
                    new OA\Property(property: 'description', type: 'string', example: '27 cali Full HD'),
                    new OA\Property(property: 'quantity', type: 'integer', example: 10),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 799.99),
                    new OA\Property(property: 'categoryid', type: 'integer', example: 3)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Sprzęt został dodany'),
            new OA\Response(response: 400, description: 'Błędne dane wejściowe')
        ]
    )]
    #[OA\Tag(name: 'Sprzęt')]
    public function addEquipment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['name'], $data['quantity'], $data['price'])) {
            throw new BadRequestHttpException('Brak wymaganych pól');
        }
    
        $equipment = $this->equipmentService->create($data);
        return $this->json(['message' => 'Dodano nowy sprzęt', 'data' => $equipment], 201);
    }
    

    #[Route('/{id}', name: 'edit_equipment', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Aktualizuje sprzęt',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'quantity', type: 'integer'),
                    new OA\Property(property: 'price', type: 'number', format: 'float'),
                    new OA\Property(property: 'categoryid', type: 'integer')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Sprzęt został zaktualizowany'),
            new OA\Response(response: 404, description: 'Nie znaleziono sprzętu')
        ]
    )]
    #[OA\Tag(name: 'Sprzęt')]
    public function editEquipment(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $equipment = $this->equipmentService->update($id, $data);
    
        if (!$equipment) {
            throw new NotFoundHttpException('Sprzęt nie znaleziony');
        }
    
        return $this->json(['message' => 'Sprzęt został zaktualizowany', 'data' => $equipment]);
    }
    

    #[Route('/{id}', name: 'delete_equipment', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuwa sprzęt po ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Sprzęt został usunięty'),
            new OA\Response(response: 404, description: 'Nie znaleziono sprzętu')
        ]
    )]
    #[OA\Tag(name: 'Sprzęt')]
    public function deleteEquipment(int $id): JsonResponse
    {
        if (!$this->equipmentService->delete($id)) {
            throw new NotFoundHttpException('Sprzęt nie znaleziony');
        }
    
        return $this->json(['message' => 'Sprzęt został usunięty']);
    }
    
}
