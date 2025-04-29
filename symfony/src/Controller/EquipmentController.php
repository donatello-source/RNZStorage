<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/equipment')]
final class EquipmentController extends AbstractController
{
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
    public function equipmentAll(EquipmentRepository $equipmentRepository, CategoryRepository $categoryRepository): JsonResponse
    {
        $equipments = $equipmentRepository->findAll();
        $data = array_map(function($equipment) use ($categoryRepository) {
            $catId = $equipment->getCategoryId();
            return [
                'id' => $equipment->getId(),
                'name' => $equipment->getName(),
                'description' => $equipment->getDescription(),
                'quantity' => $equipment->getQuantity(),
                'price' => $equipment->getPrice(),
                'categoryid' => $catId,
                'category' => $catId ? $categoryRepository->find($catId)?->getNazwa() : null,
            ];
        }, $equipments);

        return $this->json($data, 200);
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
    public function getEquipmentById(
        int $id,
        EquipmentRepository $equipmentRepository,
        CategoryRepository $categoryRepository
    ): JsonResponse {
        $equipment = $equipmentRepository->find($id);

        if (!$equipment) {
            return $this->json(['error' => 'Sprzęt nie znaleziony'], 404);
        }

        $category = $equipment->getCategoryId()
            ? $categoryRepository->find($equipment->getCategoryId())?->getNazwa()
            : null;

        $data = [
            'id' => $equipment->getId(),
            'name' => $equipment->getName(),
            'description' => $equipment->getDescription(),
            'quantity' => $equipment->getQuantity(),
            'price' => $equipment->getPrice(),
            'categoryid' => $equipment->getCategoryId(),
            'category' => $category
        ];

        return $this->json($data, 200);
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
    public function getEquipmentByCategory(
        int $categoryId,
        EquipmentRepository $equipmentRepository,
        CategoryRepository $categoryRepository
    ): JsonResponse {
        $category = $categoryRepository->find($categoryId);
        if (!$category) {
            return $this->json(['error' => 'Kategoria nie znaleziona'], 404);
        }
    
        $equipments = $equipmentRepository->findBy(['categoryId' => $categoryId]);
    
        $data = array_map(function ($equipment) use ($category) {
            return [
                'id' => $equipment->getId(),
                'name' => $equipment->getName(),
                'description' => $equipment->getDescription(),
                'quantity' => $equipment->getQuantity(),
                'price' => $equipment->getPrice(),
                'categoryid' => $equipment->getCategoryId(),
                'category' => $category->getNazwa(),
            ];
        }, $equipments);
    
        return $this->json($data, 200);
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
    public function addEquipment(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['quantity'], $data['price'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $equipment = new Equipment();
        $equipment->setName($data['name']);
        $equipment->setDescription($data['description'] ?? null);
        $equipment->setQuantity($data['quantity']);
        $equipment->setPrice($data['price']);
        $equipment->setCategoryId($data['categoryid'] ?? null);

        $entityManager->persist($equipment);
        $entityManager->flush();

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
    public function editEquipment(int $id, Request $request, EquipmentRepository $equipmentRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $equipment = $equipmentRepository->find($id);
        if (!$equipment) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $equipment->setName($data['name']);
        }
        if (isset($data['description'])) {
            $equipment->setDescription($data['description']);
        }
        if (isset($data['quantity'])) {
            $equipment->setQuantity($data['quantity']);
        }
        if (isset($data['price'])) {
            $equipment->setPrice($data['price']);
        }
        if (isset($data['categoryid'])) {
            $equipment->setCategoryId($data['categoryid']);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Sprzęt został zaktualizowany', 'data' => $equipment], 200);
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
    public function deleteEquipment(int $id, EquipmentRepository $equipmentRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $equipment = $equipmentRepository->find($id);
        if (!$equipment) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        $entityManager->remove($equipment);
        $entityManager->flush();

        return $this->json(['message' => 'Sprzęt został usunięty'], 200);
    }
}
