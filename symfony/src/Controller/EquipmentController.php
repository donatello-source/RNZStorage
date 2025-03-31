<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Equipment;
use App\Repository\EquipmentRepository;

#[Route('/api/equipment')]
class EquipmentController extends AbstractController
{
    #[Route('', name: 'equipment_all', methods: ['GET'])]
    public function equipmentAll(EquipmentRepository $equipmentRepository): JsonResponse
    {
        $equipments = $equipmentRepository->findAll();
        return $this->json($equipments, 200);
    }

    #[Route('/{id}', name: 'get_equipment_by_id', methods: ['GET'])]
    public function getEquipmentById(int $id, EquipmentRepository $equipmentRepository): JsonResponse
    {
        $equipment = $equipmentRepository->find($id);
        if (!$equipment) {
            return $this->json(['error' => 'Item not found'], 404);
        }
        return $this->json($equipment, 200);
    }

    #[Route('/category/{category}', name: 'get_equipment_by_category', methods: ['GET'])]
    public function getEquipmentByCategory(string $category, EquipmentRepository $equipmentRepository): JsonResponse
    {
        $equipments = $equipmentRepository->findBy(['category' => $category]);
        return $this->json($equipments, 200);
    }

    #[Route('', name: 'add_equipment', methods: ['POST'])]
    public function addEquipment(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nazwa'], $data['ilosc'], $data['cena'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $equipment = new Equipment();
        $equipment->setNazwa($data['nazwa']);
        $equipment->setOpis($data['opis'] ?? null);
        $equipment->setIlosc($data['ilosc']);
        $equipment->setCena($data['cena']);

        $entityManager->persist($equipment);
        $entityManager->flush();

        return $this->json(['message' => 'Dodano nowy sprzęt', 'data' => $equipment], 201);
    }

    #[Route('/{id}', name: 'edit_equipment', methods: ['PUT'])]
    public function editEquipment(int $id, Request $request, EquipmentRepository $equipmentRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $equipment = $equipmentRepository->find($id);
        if (!$equipment) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['nazwa'])) {
            $equipment->setNazwa($data['nazwa']);
        }
        if (isset($data['opis'])) {
            $equipment->setOpis($data['opis']);
        }
        if (isset($data['ilosc'])) {
            $equipment->setIlosc($data['ilosc']);
        }
        if (isset($data['cena'])) {
            $equipment->setCena($data['cena']);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Sprzęt został zaktualizowany', 'data' => $equipment], 200);
    }

    #[Route('/{id}', name: 'delete_equipment', methods: ['DELETE'])]
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
