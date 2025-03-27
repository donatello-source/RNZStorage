<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class QuotationController extends AbstractController
{

    private array $quotation = [
        "firma" => "Event Solutions",
        "cena_calosc" => 15200,
        "sprzet" => [
            ["id" => 1, "ilosc" => 2, "rabat" => 5],
            ["id" => 3, "ilosc" => 4, "rabat" => 10],
            ["id" => 5, "ilosc" => 1, "rabat" => 0],
            ["id" => 7, "ilosc" => 3, "rabat" => 15]
        ],
        "adres" => "ul. Marszałkowska 10, Warszawa",
        "dodatkowe_info" => "Wymagana dostawa i montaż."
    ];



    #[Route('/api/quotation', name: 'get_quotation', methods: ['GET'])]
    public function getQuotation(): JsonResponse
    {
        return $this->json($this->quotation, 200);
    }

    #[Route('/api/quotation/equipment', name: 'get_quotation_equipment', methods: ['GET'])]
    public function getQuotationEquipment(): JsonResponse
    {
        return $this->json($this->quotation['sprzet'], 200);
    }

    #[Route('/api/quotation/equipment/{id}', name: 'get_quotation_equipment_by_id', methods: ['GET'])]
    public function getQuotationEquipmentById(int $id): JsonResponse
    {
        foreach ($this->quotation['sprzet'] as $item) {
            if ($item['id'] === $id) {
                return $this->json($item, 200);
            }
        }
        return $this->json(['error' => 'Item not found in quotation'], 404);
    }
}
