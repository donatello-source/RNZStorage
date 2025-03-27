<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class EquipmentController extends AbstractController
{
    private array$equipment = [
    ["id" => 1, "nazwa" => "Laptop Dell XPS", "opis" => "Wydajny laptop do prezentacji", "kategoria" => "Laptopy", "cena" => 4500, "ilosc" => 3],
    ["id" => 2, "nazwa" => "Telewizor Samsung 55\"", "opis" => "Ekran 4K idealny do pokazów", "kategoria" => "Ekrany", "cena" => 3500, "ilosc" => 2],
    ["id" => 3, "nazwa" => "Mikrofon Shure SM58", "opis" => "Profesjonalny mikrofon dynamiczny", "kategoria" => "Audio", "cena" => 800, "ilosc" => 5],
    ["id" => 4, "nazwa" => "Głośnik JBL EON", "opis" => "Aktywny głośnik 1000W", "kategoria" => "Audio", "cena" => 2500, "ilosc" => 4],
    ["id" => 5, "nazwa" => "Projektor Epson EH-TW7000", "opis" => "Projektor Full HD do prezentacji", "kategoria" => "Projektory", "cena" => 4200, "ilosc" => 2],
    ["id" => 6, "nazwa" => "Mikser audio Yamaha MG10XU", "opis" => "Kompaktowy mikser audio z efektami", "kategoria" => "Audio", "cena" => 1800, "ilosc" => 1],
    ["id" => 7, "nazwa" => "Statyw oświetleniowy", "opis" => "Solidny statyw pod reflektory", "kategoria" => "Oświetlenie", "cena" => 500, "ilosc" => 6],
    ["id" => 8, "nazwa" => "Reflektor LED PAR 64", "opis" => "Kolorowy reflektor LED", "kategoria" => "Oświetlenie", "cena" => 700, "ilosc" => 8],
    ["id" => 9, "nazwa" => "Kamera Sony Alpha 7 III", "opis" => "Profesjonalna kamera do nagrań", "kategoria" => "Video", "cena" => 9000, "ilosc" => 1],
    ["id" => 10, "nazwa" => "Ekran projekcyjny 120\"", "opis" => "Duży ekran do projektora", "kategoria" => "Projektory", "cena" => 1600, "ilosc" => 2]
    ];




    #[Route('/api/equipment', name: 'equipment_all', methods: ['GET'])]
    public function equipmentAll(): JsonResponse
    {
        return $this->json($this->equipment, 200);
    }

    #[Route('/api/equipment/{id}', name: 'get_equipment_by_id', methods: ['GET'])]
    public function getEquipmentById(int $id): JsonResponse
    {
        foreach ($this->equipment as $item) {
            if ($item['id'] === $id) {
                return $this->json($item, 200);
            }
        }
        return $this->json(['error' => 'Item not found'], 404);
    }

    #[Route('/api/equipment/category/{category}', name: 'get_equipment_by_category', methods: ['GET'])]
    public function getEquipmentByCategory(string $category): JsonResponse
    {
        $filtered = array_filter($this->equipment, fn($item) => strtolower($item['kategoria']) === strtolower($category));

        return $this->json(array_values($filtered), 200);
    }

    #[Route('/api/equipment', name: 'add_equipment', methods: ['POST'])]
    public function addEquipment(): JsonResponse
    {
        return $this->json([
            'message' => 'Dodano nowy sprzęt',
            'data' => null
        ], 200);
    }
}
