<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function getAll(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function add(string $nazwa): Category
    {
        $category = new Category();
        $category->setNazwa($nazwa);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    public function delete(int $id): void
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            throw new \RuntimeException('Kategoria nie została znaleziona');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
