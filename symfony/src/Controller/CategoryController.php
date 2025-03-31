<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/category')]
final class CategoryController extends AbstractController
{
    #[Route('', name: 'category_all', methods: ['GET'])]
    public function categoryAll(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        return $this->json($categories, 200);
    }

    #[Route('', name: 'add_category', methods: ['POST'])]
    public function addCategory(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nazwa'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $category = new Category();
        $category->setNazwa($data['nazwa']);

        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json(['message' => 'Category added successfully', 'data' => $category], 201);
    }

    #[Route('/{id}', name: 'delete_category', methods: ['DELETE'])]
    public function deleteCategory(int $id, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $category = $categoryRepository->find($id);
    
        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }
    
        $entityManager->remove($category);
        $entityManager->flush();
    
        return $this->json(['message' => 'Category deleted successfully'], 200);
    }
    
}
