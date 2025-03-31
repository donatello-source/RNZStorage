<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/api/equipment')]
final class EquipmentController extends AbstractController
{
    #[Route('', name: 'equipment_all', methods: ['GET'])]
    public function equipmentAll(EquipmentRepository $equipmentRepository, CategoryRepository $categoryRepository): JsonResponse
    {
        $equipments = $equipmentRepository->findAll();
        $data = array_map(function($equipment) use ($categoryRepository) {
            $catId = $equipment->getCategoryId();
            return [
                'id' => $equipment->getId(),
                'name' => $equipment->getName(),
                'description' => $equipment->getDescription(),
                'quantity' => $equipment->getQuantity(),
                'price' => $equipment->getPrice(),
                'categoryid' => $equipment->getCategoryId(),
                'category' => $equipment->getCategoryId() ? $categoryRepository->find($catId)->getNazwa() : null,
            ];
        }, $equipments);
    
        return $this->json($data, 200);
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
        $equipment->setName($data['nazwa']);
        $equipment->setDescription($data['opis'] ?? null);
        $equipment->setQuantity($data['ilosc']);
        $equipment->setPrice($data['cena']);
        $equipment->setCategory($data['kategoria'] ?? null);

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
        
        if (isset($data['name'])) {
            $equipment->setName($data['name']);
        }
        if (isset($data['description'])) {
            $equipment->setDescription($data['description']);
        }
        if (isset($data['quantity'])) {
            $equipment->setQuantity($data['quantity']);
        }
        if (isset($data['price'])) {
            $equipment->setPrice($data['price']);
        }
        if (isset($data['categoryid'])) {
            $equipment->setCategoryId($data['categoryid']);
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
