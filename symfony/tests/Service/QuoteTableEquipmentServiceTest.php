<?php

namespace App\Tests\Service;

use App\Entity\QuoteTable;
use App\Entity\Equipment;
use App\Entity\QuoteTableEquipment;
use App\Service\QuoteTableEquipmentService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class QuoteTableEquipmentServiceTest extends TestCase
{
    private $entityManagerMock;
    private $repositoryMock;
    private QuoteTableEquipmentService $service;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->repositoryMock = $this->createMock(EntityRepository::class);

        $this->entityManagerMock->method('getRepository')
            ->with(QuoteTableEquipment::class)
            ->willReturn($this->repositoryMock);

        $this->service = new QuoteTableEquipmentService($this->entityManagerMock);
    }

    public function testCreatePersistsAndFlushes(): void
    {
        $table = new QuoteTable();
        $equipment = new Equipment();

        $this->entityManagerMock->expects($this->once())->method('persist')->with($this->isInstanceOf(QuoteTableEquipment::class));
        $this->entityManagerMock->expects($this->once())->method('flush');

        $qte = $this->service->create($table, $equipment, 2, 3, 10.5, true);

        $this->assertInstanceOf(QuoteTableEquipment::class, $qte);
        $this->assertSame($table, $qte->getQuoteTable());
        $this->assertSame($equipment, $qte->getEquipment());
        $this->assertEquals(2, $qte->getCount());
        $this->assertEquals(3, $qte->getDays());
        $this->assertEquals(10.5, $qte->getDiscount());
        $this->assertTrue($qte->isShowComment());
    }

    public function testFindReturnsEntity(): void
    {
        $qte = new QuoteTableEquipment();
        $this->repositoryMock->expects($this->once())->method('find')->with(5)->willReturn($qte);

        $result = $this->service->find(5);
        $this->assertSame($qte, $result);
    }

    public function testUpdateChangesFieldsAndFlushes(): void
    {
        $qte = new QuoteTableEquipment();
        $qte->setCount(1);
        $qte->setDays(1);
        $qte->setDiscount(0);
        $qte->setShowComment(false);

        $this->entityManagerMock->expects($this->once())->method('flush');

        $updated = $this->service->update($qte, 5, 7, 15.5, true);

        $this->assertEquals(5, $updated->getCount());
        $this->assertEquals(7, $updated->getDays());
        $this->assertEquals(15.5, $updated->getDiscount());
        $this->assertTrue($updated->isShowComment());
    }

    public function testDeleteRemovesAndFlushes(): void
    {
        $qte = new QuoteTableEquipment();

        $this->entityManagerMock->expects($this->once())->method('remove')->with($qte);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->service->delete($qte);
        $this->assertTrue(true);
    }

    public function testFindByTableReturnsArray(): void
    {
        $expected = [new QuoteTableEquipment(), new QuoteTableEquipment()];
        $this->repositoryMock->expects($this->once())
            ->method('findBy')
            ->with(['quoteTable' => 10])
            ->willReturn($expected);

        $result = $this->service->findByTable(10);
        $this->assertSame($expected, $result);
    }
}