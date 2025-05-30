<?php

namespace App\Controller;

use App\Entity\QuoteTable;
use App\Entity\Equipment;
use App\Service\QuoteTableEquipmentService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/quote-table-equipment')]
class QuoteTableEquipmentController extends AbstractController
{
    public function __construct(private readonly QuoteTableEquipmentService $service) {}

    #[Route('/create', name: 'quote_table_equipment_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaj sprzęt do tabelki',
        tags: ['QuoteTableEquipment'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quote_table_id', 'equipment_id', 'count', 'days', 'discount', 'show_comment'],
                properties: [
                    new OA\Property(property: 'quote_table_id', type: 'integer', example: 1),
                    new OA\Property(property: 'equipment_id', type: 'integer', example: 2),
                    new OA\Property(property: 'count', type: 'integer', example: 3),
                    new OA\Property(property: 'days', type: 'integer', example: 2),
                    new OA\Property(property: 'discount', type: 'number', example: 10),
                    new OA\Property(property: 'show_comment', type: 'boolean', example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Dodano sprzęt'),
            new OA\Response(response: 404, description: 'Nie znaleziono tabelki lub sprzętu'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $table = $this->getDoctrine()->getRepository(QuoteTable::class)->find($data['quote_table_id']);
        $equipment = $this->getDoctrine()->getRepository(Equipment::class)->find($data['equipment_id']);
        if (!$table || !$equipment) {
            return $this->json(['error' => 'Table or Equipment not found'], 404);
        }
        $qte = $this->service->create(
            $table,
            $equipment,
            $data['count'],
            $data['days'],
            $data['discount'],
            $data['show_comment']
        );
        return $this->json(['id' => $qte->getId()], 201);
    }

    #[Route('/{id}', name: 'quote_table_equipment_get', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz sprzęt z tabelki po ID',
        tags: ['QuoteTableEquipment'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Szczegóły sprzętu',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'count', type: 'integer', example: 3),
                        new OA\Property(property: 'days', type: 'integer', example: 2),
                        new OA\Property(property: 'discount', type: 'number', example: 10),
                        new OA\Property(property: 'show_comment', type: 'boolean', example: true)
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono sprzętu'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function get(int $id): JsonResponse
    {
        $qte = $this->service->find($id);
        if (!$qte) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json([
            'id' => $qte->getId(),
            'count' => $qte->getCount(),
            'days' => $qte->getDays(),
            'discount' => $qte->getDiscount(),
            'show_comment' => $qte->isShowComment()
        ]);
    }

    #[Route('/{id}', name: 'quote_table_equipment_update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Aktualizuj sprzęt w tabelce',
        tags: ['QuoteTableEquipment'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['count', 'days', 'discount', 'show_comment'],
                properties: [
                    new OA\Property(property: 'count', type: 'integer', example: 3),
                    new OA\Property(property: 'days', type: 'integer', example: 2),
                    new OA\Property(property: 'discount', type: 'number', example: 10),
                    new OA\Property(property: 'show_comment', type: 'boolean', example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Zaktualizowano sprzęt'),
            new OA\Response(response: 404, description: 'Nie znaleziono sprzętu'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $qte = $this->service->find($id);
        if (!$qte) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $this->service->update(
            $qte,
            $data['count'],
            $data['days'],
            $data['discount'],
            $data['show_comment']
        );
        return $this->json(['message' => 'Zaktualizowano sprzęt']);
    }

    #[Route('/{id}', name: 'quote_table_equipment_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń sprzęt z tabelki',
        tags: ['QuoteTableEquipment'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usunięto sprzęt'),
            new OA\Response(response: 404, description: 'Nie znaleziono sprzętu'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $qte = $this->service->find($id);
        if (!$qte) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $this->service->delete($qte);
        return $this->json(['message' => 'Usunięto sprzęt']);
    }

    #[Route('/list/{tableId}', name: 'quote_table_equipment_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz wszystkie sprzęty dla tabelki',
        tags: ['QuoteTableEquipment'],
        parameters: [
            new OA\Parameter(name: 'tableId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista sprzętów',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'count', type: 'integer', example: 3),
                            new OA\Property(property: 'days', type: 'integer', example: 2),
                            new OA\Property(property: 'discount', type: 'number', example: 10),
                            new OA\Property(property: 'show_comment', type: 'boolean', example: true)
                        ]
                    )
                )
            ),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function list(int $tableId): JsonResponse
    {
        $items = $this->service->findByTable($tableId);
        return $this->json(array_map(fn($qte) => [
            'id' => $qte->getId(),
            'count' => $qte->getCount(),
            'days' => $qte->getDays(),
            'discount' => $qte->getDiscount(),
            'show_comment' => $qte->isShowComment()
        ], $items));
    }
}