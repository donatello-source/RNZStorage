<?php

namespace App\Service;

use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class EquipmentService
{
    public function __construct(
        private EquipmentRepository $equipmentRepository,
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function getAll(): array
    {
        $equipments = $this->equipmentRepository->findAll();
        return array_map(fn($equipment) => $this->format($equipment), $equipments);
    }

    public function getById(int $id): ?array
    {
        $equipment = $this->equipmentRepository->find($id);
        return $equipment ? $this->format($equipment) : null;
    }

    public function getByCategory(int $categoryId): ?array
    {
        $category = $this->categoryRepository->find($categoryId);
        if (!$category) {
            return null;
        }

        $equipments = $this->equipmentRepository->findBy(['categoryId' => $categoryId]);

        return array_map(fn($equipment) => $this->format($equipment, $category->getNazwa()), $equipments);
    }

    public function create(array $data): Equipment
    {
        $equipment = new Equipment();
        $equipment->setName($data['name']);
        $equipment->setDescription($data['description'] ?? null);
        $equipment->setQuantity($data['quantity']);
        $equipment->setPrice($data['price']);
        $equipment->setCategoryId($data['categoryid'] ?? null);
        $equipment->setPricingInfo($data['pricing_info'] ?? null);
        $equipment->setAdditionalInfo($data['additional_info'] ?? null);

        $this->entityManager->persist($equipment);
        $this->entityManager->flush();

        return $equipment;
    }

    public function update(int $id, array $data): ?Equipment
    {
        $equipment = $this->equipmentRepository->find($id);
        if (!$equipment) {
            return null;
        }

        foreach (['name', 'description', 'quantity', 'price', 'categoryid', 'pricing_info', 'additional_info'] as $field) {
            if (isset($data[$field])) {
                $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
                $equipment->$setter($data[$field]);
            }
        }

        $this->entityManager->flush();

        return $equipment;
    }

    public function delete(int $id): bool
    {
        $equipment = $this->equipmentRepository->find($id);
        if (!$equipment) {
            return false;
        }

        $this->entityManager->remove($equipment);
        $this->entityManager->flush();
        return true;
    }

    private function format(Equipment $equipment, ?string $categoryName = null): array
    {
        $categoryName ??= $equipment->getCategoryId()
            ? $this->categoryRepository->find($equipment->getCategoryId())?->getNazwa()
            : null;

        return [
            'id' => $equipment->getId(),
            'name' => $equipment->getName(),
            'description' => $equipment->getDescription(),
            'quantity' => $equipment->getQuantity(),
            'price' => $equipment->getPrice(),
            'categoryid' => $equipment->getCategoryId(),
            'category' => $categoryName,
            'pricing_info' => $equipment->getPricingInfo(),
            'additional_info' => $equipment->getAdditionalInfo(),
        ];
    }
}
