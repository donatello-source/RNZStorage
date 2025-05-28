<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Service\QuotationService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/quotation')]
class QuotationController extends AbstractController
{
    public function __construct(private readonly QuotationService $quotationService) {}

    #[Route('', name: 'quotation_list', methods: ['GET'])]
    #[OA\Get(summary: 'Lista wszystkich wycen')]
    public function list(): JsonResponse
    {
        $quotes = $this->quotationService->getAllQuotes();
        return $this->json(array_map(fn(Quote $q) => [
            'id' => $q->getId(),
            'company' => $q->getCompany(),
            'projekt' => $q->getProjekt(),
            'lokalizacja' => $q->getLokalizacja(),
            'status' => $q->getStatus(),
            'dataWystawienia' => $q->getDataWystawienia()?->format('Y-m-d'),
        ], $quotes));
    }

    #[Route('/{id}', name: 'quotation_get', methods: ['GET'])]
    #[OA\Get(summary: 'Szczegóły wyceny')]
    public function get(int $id): JsonResponse
    {
        $q = $this->quotationService->getQuoteById($id);
        return $this->json([
            'id' => $q->getId(),
            'company' => $q->getCompany(),
            'projekt' => $q->getProjekt(),
            'lokalizacja' => $q->getLokalizacja(),
            'status' => $q->getStatus(),
            'dataWystawienia' => $q->getDataWystawienia()?->format('Y-m-d'),
            // Możesz dodać tu relacje (daty, tabelki, sprzęt) jeśli chcesz
        ]);
    }

    #[Route('', name: 'quotation_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaj nową wycenę',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['company_id', 'projekt', 'lokalizacja'],
                properties: [
                    new OA\Property(property: 'company_id', type: 'integer', example: 1),
                    new OA\Property(property: 'projekt', type: 'string', example: 'Projekt X'),
                    new OA\Property(property: 'lokalizacja', type: 'string', example: 'Warszawa'),
                    new OA\Property(property: 'global_discount', type: 'integer', example: 10),
                    new OA\Property(property: 'dates', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'type', type: 'string', example: 'single'),
                            new OA\Property(property: 'value', type: 'string', example: '2025-06-01'),
                            new OA\Property(property: 'comment', type: 'string', example: 'Montaż')
                        ]
                    )),
                    new OA\Property(property: 'tables', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'label', type: 'string', example: 'Sala A'),
                            new OA\Property(property: 'discount', type: 'integer', example: 5),
                            new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'equipment_id', type: 'integer', example: 2),
                                    new OA\Property(property: 'count', type: 'integer', example: 3),
                                    new OA\Property(property: 'days', type: 'integer', example: 2),
                                    new OA\Property(property: 'discount', type: 'integer', example: 0),
                                    new OA\Property(property: 'show_comment', type: 'boolean', example: true)
                                ]
                            ))
                        ]
                    ))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Dodano wycenę'),
            new OA\Response(response: 400, description: 'Błąd walidacji')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $quote = $this->quotationService->addQuote($data);
        return $this->json(['id' => $quote->getId()], 201);
    }

    #[Route('/{id}', name: 'quotation_delete', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Usuń wycenę')]
    public function delete(int $id): JsonResponse
    {
        $this->quotationService->deleteQuote($id);
        return $this->json(['message' => 'Usunięto wycenę']);
    }
}
