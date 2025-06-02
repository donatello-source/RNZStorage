<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Entity\QuoteTable;
use App\Service\QuoteTableService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/quote-table')]
class QuoteTableController extends AbstractController
{
    public function __construct(
        private readonly QuoteTableService $service,
        private readonly EntityManagerInterface $em
    ) {}

    #[Route('/create', name: 'quote_table_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaj tabelkę do wyceny',
        tags: ['QuoteTable'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quote_id', 'label', 'discount'],
                properties: [
                    new OA\Property(property: 'quote_id', type: 'integer', example: 1),
                    new OA\Property(property: 'label', type: 'string', example: 'Sala A'),
                    new OA\Property(property: 'discount', type: 'number', example: 5)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Dodano tabelkę'),
            new OA\Response(response: 404, description: 'Nie znaleziono wyceny'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $quote = $this->em->getRepository(Quote::class)->find($data['quote_id']);
        if (!$quote) {
            return $this->json(['error' => 'Quote not found'], 404);
        }
        $table = $this->service->create($quote, $data['label'], $data['discount']);
        return $this->json(['id' => $table->getId()], 201);
    }

    #[Route('/{id}', name: 'quote_table_get', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz tabelkę po ID',
        tags: ['QuoteTable'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Szczegóły tabelki',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'label', type: 'string', example: 'Sala A'),
                        new OA\Property(property: 'discount', type: 'number', example: 5)
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono tabelki'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function get(int $id): JsonResponse
    {
        $table = $this->service->find($id);
        if (!$table) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json([
            'id' => $table->getId(),
            'label' => $table->getLabel(),
            'discount' => $table->getDiscount()
        ]);
    }

    #[Route('/{id}', name: 'quote_table_update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Aktualizuj tabelkę',
        tags: ['QuoteTable'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['label', 'discount'],
                properties: [
                    new OA\Property(property: 'label', type: 'string', example: 'Sala B'),
                    new OA\Property(property: 'discount', type: 'number', example: 10)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Zaktualizowano tabelkę'),
            new OA\Response(response: 404, description: 'Nie znaleziono tabelki'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $table = $this->service->find($id);
        if (!$table) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $this->service->update($table, $data['label'], $data['discount']);
        return $this->json(['message' => 'Zaktualizowano tabelkę']);
    }

    #[Route('/{id}', name: 'quote_table_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń tabelkę',
        tags: ['QuoteTable'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usunięto tabelkę'),
            new OA\Response(response: 404, description: 'Nie znaleziono tabelki'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $table = $this->service->find($id);
        if (!$table) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $this->service->delete($table);
        return $this->json(['message' => 'Usunięto tabelkę']);
    }

    #[Route('/list/{quoteId}', name: 'quote_table_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz wszystkie tabelki dla wyceny',
        tags: ['QuoteTable'],
        parameters: [
            new OA\Parameter(name: 'quoteId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista tabelek',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'label', type: 'string', example: 'Sala A'),
                            new OA\Property(property: 'discount', type: 'number', example: 5)
                        ]
                    )
                )
            ),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function list(int $quoteId): JsonResponse
    {
        $tables = $this->service->findByQuote($quoteId);
        return $this->json(array_map(fn($t) => [
            'id' => $t->getId(),
            'label' => $t->getLabel(),
            'discount' => $t->getDiscount()
        ], $tables));
    }
}