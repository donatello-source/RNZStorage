<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Service\QuoteDateService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/quote-date')]
class QuoteDateController extends AbstractController
{
    public function __construct(private readonly QuoteDateService $service) {}

    #[Route('/create', name: 'quote_date_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaj datę do wyceny',
        tags: ['Quote Dates'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quote_id', 'type', 'value'],
                properties: [
                    new OA\Property(property: 'quote_id', type: 'integer', example: 1),
                    new OA\Property(property: 'type', type: 'string', example: 'single'),
                    new OA\Property(property: 'value', type: 'string', example: '2025-06-01'),
                    new OA\Property(property: 'comment', type: 'string', example: 'Montaż', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Dodano datę'),
            new OA\Response(response: 404, description: 'Nie znaleziono wyceny'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $quote = $this->getDoctrine()->getRepository(Quote::class)->find($data['quote_id']);
        if (!$quote) {
            return $this->json(['error' => 'Quote not found'], 404);
        }
        $date = $this->service->create($quote, $data['type'], $data['value'], $data['comment'] ?? null);
        return $this->json(['id' => $date->getId()], 201);
    }

    #[Route('/{id}', name: 'quote_date_get', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz datę po ID',
        tags: ['Quote Dates'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Szczegóły daty',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'type', type: 'string', example: 'single'),
                        new OA\Property(property: 'value', type: 'string', example: '2025-06-01'),
                        new OA\Property(property: 'comment', type: 'string', example: 'Montaż', nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono daty'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function get(int $id): JsonResponse
    {
        $date = $this->service->find($id);
        if (!$date) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json([
            'id' => $date->getId(),
            'type' => $date->getType(),
            'value' => $date->getValue(),
            'comment' => $date->getComment()
        ]);
    }

    #[Route('/{id}', name: 'quote_date_update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Aktualizuj datę',
        tags: ['Quote Dates'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'value'],
                properties: [
                    new OA\Property(property: 'type', type: 'string', example: 'range'),
                    new OA\Property(property: 'value', type: 'string', example: '2025-06-01 - 2025-06-03'),
                    new OA\Property(property: 'comment', type: 'string', example: 'Realizacja', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Zaktualizowano datę'),
            new OA\Response(response: 404, description: 'Nie znaleziono daty'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $date = $this->service->find($id);
        if (!$date) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $this->service->update($date, $data['type'], $data['value'], $data['comment'] ?? null);
        return $this->json(['message' => 'Zaktualizowano datę']);
    }

    #[Route('/{id}', name: 'quote_date_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń datę',
        tags: ['Quote Dates'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usunięto datę'),
            new OA\Response(response: 404, description: 'Nie znaleziono daty'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')

        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $date = $this->service->find($id);
        if (!$date) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $this->service->delete($date);
        return $this->json(['message' => 'Usunięto datę']);
    }

    #[Route('/list/{quoteId}', name: 'quote_date_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz wszystkie daty dla wyceny',
        tags: ['Quote Dates'],
        parameters: [
            new OA\Parameter(name: 'quoteId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista dat',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'type', type: 'string', example: 'single'),
                            new OA\Property(property: 'value', type: 'string', example: '2025-06-01'),
                            new OA\Property(property: 'comment', type: 'string', example: 'Montaż', nullable: true)
                        ]
                    )
                )
            )
        ]
    )]
    public function list(int $quoteId): JsonResponse
    {
        $dates = $this->service->findByQuote($quoteId);
        return $this->json(array_map(fn($d) => [
            'id' => $d->getId(),
            'type' => $d->getType(),
            'value' => $d->getValue(),
            'comment' => $d->getComment()
        ], $dates));
    }
}