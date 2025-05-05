<?php

namespace App\Tests\Service;

use App\Entity\Equipment;
use App\Entity\Category;
use App\Repository\EquipmentRepository;
use App\Repository\CategoryRepository;
use App\Service\EquipmentService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class EquipmentServiceTest extends TestCase
{
    private EquipmentService $equipmentService;
    private $equipmentRepositoryMock;
    private $categoryRepositoryMock;
    private $entityManagerMock;

    protected function setUp(): void
    {
        $this->equipmentRepositoryMock = $this->createMock(EquipmentRepository::class);
        $this->categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->equipmentService = new EquipmentService(
            $this->equipmentRepositoryMock,
            $this->categoryRepositoryMock,
            $this->entityManagerMock
        );
    }

    public function testGetAllReturnsFormattedArray(): void
    {
        $equipment = new Equipment();
        $equipment->setName('Laptop');
        $equipment->setQuantity(5);
        $equipment->setPrice(1000);
        $equipment->setCategoryId(1);

        $this->equipmentRepositoryMock
            ->method('findAll')
            ->willReturn([$equipment]);

        $this->categoryRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn((new Category())->setNazwa('Elektronika'));

        $result = $this->equipmentService->getAll();

        $this->assertIsArray($result);
        $this->assertEquals('Laptop', $result[0]['name']);
        $this->assertEquals('Elektronika', $result[0]['category']);
    }

    public function testGetByIdReturnsFormattedEquipment(): void
    {
        $equipment = new Equipment();
        $equipment->setName('Monitor');
        $equipment->setQuantity(10);
        $equipment->setPrice(200);
        $equipment->setCategoryId(null);
    
        $this->equipmentRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($equipment);
    
        $result = $this->equipmentService->getById(1);
    
        $this->assertIsArray($result);
        $this->assertEquals('Monitor', $result['name']);
    }
    

    public function testGetByCategoryReturnsFormattedList(): void
    {
        $category = (new Category())->setNazwa('Audio');

        $equipment1 = new Equipment();
        $equipment1->setName('Mikrofon');
        $equipment1->setQuantity(3);
        $equipment1->setPrice(99.99);
        $equipment1->setCategoryId(2);

        $this->categoryRepositoryMock
            ->method('find')
            ->with(2)
            ->willReturn($category);

        $this->equipmentRepositoryMock
            ->method('findBy')
            ->with(['categoryId' => 2])
            ->willReturn([$equipment1]);

        $result = $this->equipmentService->getByCategory(2);

        $this->assertIsArray($result);
        $this->assertEquals('Mikrofon', $result[0]['name']);
        $this->assertEquals('Audio', $result[0]['category']);
    }


    public function testCreatePersistsNewEquipment(): void
    {
        $data = [
            'name' => 'Tablet',
            'quantity' => 10,
            'price' => 500,
            'categoryid' => 3
        ];

        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Equipment::class));

        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $equipment = $this->equipmentService->create($data);

        $this->assertInstanceOf(Equipment::class, $equipment);
        $this->assertEquals('Tablet', $equipment->getName());
        $this->assertEquals(10, $equipment->getQuantity());
    }

    public function testUpdateModifiesExistingEquipment(): void
    {
        $equipment = new Equipment();
        $equipment->setName('Old Name');

        $this->equipmentRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($equipment);

        $updated = $this->equipmentService->update(1, ['name' => 'New Name']);

        $this->assertInstanceOf(Equipment::class, $updated);
        $this->assertEquals('New Name', $updated->getName());
    }

    public function testUpdateReturnsNullIfNotFound(): void
    {
        $this->equipmentRepositoryMock
            ->method('find')
            ->with(99)
            ->willReturn(null);

        $result = $this->equipmentService->update(99, ['name' => 'Does Not Matter']);
        $this->assertNull($result);
    }

    public function testDeleteRemovesEquipment(): void
    {
        $equipment = new Equipment();

        $this->equipmentRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($equipment);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('remove')
            ->with($equipment);
        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $result = $this->equipmentService->delete(1);
        $this->assertTrue($result);
    }

    public function testDeleteReturnsFalseWhenNotFound(): void
    {
        $this->equipmentRepositoryMock
            ->method('find')
            ->with(1234)
            ->willReturn(null);

        $result = $this->equipmentService->delete(1234);
        $this->assertFalse($result);
    }
}
