<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryServiceTest extends TestCase
{
    private CategoryService $categoryService;
    private $categoryRepositoryMock;
    private $entityManagerMock;

    protected function setUp(): void
    {
        $this->categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->categoryService = new CategoryService(
            $this->categoryRepositoryMock,
            $this->entityManagerMock
        );
    }

    public function testGetAllReturnsCategories(): void
    {
        $categories = [new Category(), new Category()];
        $this->categoryRepositoryMock
            ->method('findAll')
            ->willReturn($categories);

        $result = $this->categoryService->getAll();

        $this->assertSame($categories, $result);
    }

    public function testAddPersistsCategory(): void
    {
        $nazwa = 'Test Category';

        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Category::class));

        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $category = $this->categoryService->add($nazwa);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals($nazwa, $category->getNazwa());
    }

    public function testDeleteExistingCategory(): void
    {
        $category = new Category();

        $this->categoryRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($category);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('remove')
            ->with($category);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $this->categoryService->delete(1);
    }

    public function testDeleteThrowsExceptionWhenCategoryNotFound(): void
    {
        $this->categoryRepositoryMock
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->categoryService->delete(999);
    }
}
