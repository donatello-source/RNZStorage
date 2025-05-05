<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Kategorie')]
final class CategoryController extends AbstractController
{
    public function __construct(private readonly CategoryService $categoryService) {}

    #[Route('/api/category', name: 'category_all', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista wszystkich kategorii',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca listę kategorii',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nazwa', type: 'string', example: 'Video')
                        ]
                    )
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Kategorie')]
    public function categoryAll(): JsonResponse
    {
        $categories = $this->categoryService->getAll();
        return $this->json($categories, 200, [], ['groups' => ['category:read']]);
    }


    #[Route('/api/category', name: 'add_category', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaje nową kategorię',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['nazwa'],
                properties: [
                    new OA\Property(property: 'nazwa', type: 'string', example: 'Udźwiękowenie')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Pomyślnie dodano kategorię',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 5),
                        new OA\Property(property: 'nazwa', type: 'string', example: 'Udźwiękowenie')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Brak wymaganych danych'
            )
        ]
    )]
    #[OA\Tag(name: 'Kategorie')]
    public function addCategory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nazwa'])) {
            return $this->json(['error' => 'Missing required field: nazwa'], 400);
        }

        $category = $this->categoryService->add($data['nazwa']);

        return $this->json($category, 201, [], ['groups' => ['category:read']]);
    }


    #[Route('/api/category/{id}', name: 'delete_category', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuwa kategorię po ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID kategorii',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kategoria została usunięta',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Kategoria została usunięta')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Kategoria nie została znaleziona'
            )
        ]
    )]
    #[OA\Tag(name: 'Kategorie')]
    public function deleteCategory(int $id): JsonResponse
    {
        try {
            $this->categoryService->delete($id);
            return $this->json(['message' => 'Kategoria została usunięta'], 200);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }
}
