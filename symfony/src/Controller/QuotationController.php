<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Entity\QuoteEquipment;
use App\Repository\QuoteRepository;
use App\Repository\QuoteEquipmentRepository;
use App\Service\QuotationService;
use Doctrine\ORM\EntityManagerInterface;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/quotation')]
class QuotationController extends AbstractController
{
    public function __construct(private readonly QuotationService $quotationService) {}
    #[Route('', name: 'get_quotation', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz wszystkie wyceny',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista wycen',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'company', type: 'integer', example: 1),
                            new OA\Property(property: 'status', type: 'string', example: 'nowa'),
                            new OA\Property(property: 'dane_kontaktowe', type: 'string', example: 'Jan Kowalski, 123-456-789'),
                            new OA\Property(property: 'miejsce', type: 'string', example: 'Warszawa'),
                            new OA\Property(property: 'data_wystawienia', type: 'string', format: 'date-time', example: '2024-04-25T12:00:00')
                        ]
                    )
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Wyceny')]
    public function getQuotation(): JsonResponse
    {
        $quotes = $this->quotationService->getAllQuotes();
        return $this->json($quotes, 200);
    }

    #[Route('/{id}', name: 'get_quotation_by_id', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        summary: 'Pobierz wycenę po ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Szczegóły wyceny',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'company', type: 'integer', example: 1),
                        new OA\Property(property: 'status', type: 'string', example: 'nowa'),
                        new OA\Property(property: 'dane_kontaktowe', type: 'string', example: 'Jan Kowalski, 123-456-789'),
                        new OA\Property(property: 'miejsce', type: 'string', example: 'Warszawa'),
                        new OA\Property(property: 'data_wystawienia', type: 'string', format: 'date-time', example: '2024-04-25T12:00:00')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono wyceny')
        ]
    )]
    #[OA\Tag(name: 'Wyceny')]
    public function getQuotationById(int $id): JsonResponse
    {
        $quote = $this->quotationService->getQuoteById($id);
        return $this->json($quote, 200);
    }

    #[Route('/equipment', name: 'get_quotation_equipment', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz wszystkie pozycje sprzętu z wycen',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista pozycji sprzętu',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 10),
                            new OA\Property(property: 'quote_id', type: 'integer', example: 1),
                            new OA\Property(property: 'equipment_id', type: 'integer', example: 5),
                            new OA\Property(property: 'quantity', type: 'integer', example: 3)
                        ]
                    )
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Wyceny')]
    public function getQuotationEquipment(): JsonResponse
    {
        $equipment = $this->quotationService->getAllQuoteEquipment();
        return $this->json($equipment, 200);
    }
    #[Route('/equipment/{id}', name: 'get_quotation_equipment_by_id', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz pozycję sprzętu z wyceny po ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 10)
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Szczegóły pozycji sprzętu',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 10),
                        new OA\Property(property: 'quote_id', type: 'integer', example: 1),
                        new OA\Property(property: 'equipment_id', type: 'integer', example: 5),
                        new OA\Property(property: 'quantity', type: 'integer', example: 3)
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono pozycji sprzętu')
        ]
    )]
    #[OA\Tag(name: 'Wyceny')]
    public function getQuotationEquipmentById(int $id): JsonResponse
    {
        $equipment = $this->quotationService->getQuoteEquipmentById($id);
        return $this->json($equipment, 200);
    }

    #[Route('', name: 'add_quotation', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaj nową wycenę',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['company', 'status', 'dane_kontaktowe', 'data_wystawienia', 'data_poczatek', 'data_koniec', 'miejsce'],
                properties: [
                    new OA\Property(property: 'company', type: 'integer', example: 1),
                    new OA\Property(property: 'status', type: 'string', example: 'nowa'),
                    new OA\Property(property: 'dane_kontaktowe', type: 'string', example: 'Jan Kowalski, 123-456-789'),
                    new OA\Property(property: 'data_wystawienia', type: 'string', format: 'date', example: '2025-04-28'),
                    new OA\Property(property: 'data_poczatek', type: 'string', format: 'date', example: '2025-05-01'),
                    new OA\Property(property: 'data_koniec', type: 'string', format: 'date', example: '2025-05-03'),
                    new OA\Property(property: 'miejsce', type: 'string', example: 'Warszawa')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Dodano nową wycenę'),
            new OA\Response(response: 400, description: 'Brak wymaganych pól')
        ]
    )]
    #[OA\Tag(name: 'Wyceny')]
    public function addQuotation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $quote = $this->quotationService->addQuote($data);
        return $this->json(['message' => 'Dodano nową wycenę', 'data' => $quote], 201);
    }

    #[Route('/{id}', name: 'delete_quotation', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń wycenę po ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)
        ],
        responses: [
            new OA\Response(response: 200, description: 'Wycena została usunięta'),
            new OA\Response(response: 404, description: 'Nie znaleziono wyceny')
        ]
    )]
    #[OA\Tag(name: 'Wyceny')]
    public function deleteQuotation(int $id): JsonResponse
    {
        $this->quotationService->deleteQuote($id);
        return $this->json(['message' => 'Wycena została usunięta'], 200);
    }
}
