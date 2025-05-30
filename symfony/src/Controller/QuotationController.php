<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Service\QuotationService;
use App\Entity\Company;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/quotation')]
class QuotationController extends AbstractController
{
    public function __construct(
        private readonly QuotationService $quotationService,
        private readonly EntityManagerInterface $em
    ) {}

    #[Route('', name: 'quotation_list', methods: ['GET'])]
    #[OA\Get(summary: 'Lista wszystkich wycen')]
    public function list(): JsonResponse
    {
        $quotes = $this->quotationService->getAllQuotesWithPrices();
        return $this->json($quotes);
    }

    #[Route('/{id}', name: 'quotation_get', methods: ['GET'])]
    #[OA\Get(summary: 'Szczegóły wyceny')]
    public function get(int $id): JsonResponse
    {
        $data = $this->quotationService->getQuoteDataForEdit($id);
        return $this->json($data);
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

    #[Route('/{id}/status', name: 'quotation_status_update', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Zmień status wyceny',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'przyjęta')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Status zmieniony'),
            new OA\Response(response: 404, description: 'Nie znaleziono wyceny')
        ]
    )]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? null;
        if (!$status) {
            return $this->json(['error' => 'Brak statusu'], Response::HTTP_BAD_REQUEST);
        }

        $quote = $this->quotationService->getQuoteById($id);
        if (!$quote) {
            return $this->json(['error' => 'Nie znaleziono wyceny'], Response::HTTP_NOT_FOUND);
        }

        $quote->setStatus($status);
        $this->em->flush();

        return $this->json(['message' => 'Status zmieniony']);
    }

    #[Route('/{id}', name: 'quotation_update', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Edytuj wycenę',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'zamawiajacy', type: 'integer'),
                    new OA\Property(property: 'projekt', type: 'string'),
                    new OA\Property(property: 'lokalizacja', type: 'string'),
                    new OA\Property(property: 'daty', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'type', type: 'string'),
                            new OA\Property(property: 'value', type: 'string'),
                            new OA\Property(property: 'comment', type: 'string', nullable: true)
                        ]
                    )),
                    new OA\Property(property: 'tabele', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'kategoria', type: 'string'),
                            new OA\Property(property: 'rabatTabelki', type: 'number'),
                            new OA\Property(property: 'sprzety', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'ilosc', type: 'integer'),
                                    new OA\Property(property: 'dni', type: 'integer'),
                                    new OA\Property(property: 'rabat', type: 'number'),
                                    new OA\Property(property: 'showComment', type: 'boolean')
                                ]
                            ))
                        ]
                    )),
                    new OA\Property(property: 'rabatCalkowity', type: 'number')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Zaktualizowano wycenę'),
            new OA\Response(response: 404, description: 'Nie znaleziono wyceny')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->quotationService->updateQuote($id, $data);
        return $this->json(['message' => 'Wycena zaktualizowana']);
    }
}
