<?php

namespace App\Tests\Service;

use App\Entity\Quote;
use App\Entity\QuoteTable;
use App\Service\QuoteTableService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class QuoteTableServiceTest extends TestCase
{
    private $entityManagerMock;
    private $repositoryMock;
    private QuoteTableService $service;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->repositoryMock = $this->createMock(EntityRepository::class);

        $this->entityManagerMock->method('getRepository')
            ->with(QuoteTable::class)
            ->willReturn($this->repositoryMock);

        $this->service = new QuoteTableService($this->entityManagerMock);
    }

    public function testCreatePersistsAndFlushes(): void
    {
        $quote = new Quote();

        $this->entityManagerMock->expects($this->once())->method('persist')->with($this->isInstanceOf(QuoteTable::class));
        $this->entityManagerMock->expects($this->once())->method('flush');

        $table = $this->service->create($quote, 'Sala A', 10.5);

        $this->assertInstanceOf(QuoteTable::class, $table);
        $this->assertSame($quote, $table->getQuote());
        $this->assertEquals('Sala A', $table->getLabel());
        $this->assertEquals(10.5, $table->getDiscount());
    }

    public function testFindReturnsEntity(): void
    {
        $table = new QuoteTable();
        $this->repositoryMock->expects($this->once())->method('find')->with(7)->willReturn($table);

        $result = $this->service->find(7);
        $this->assertSame($table, $result);
    }

    public function testUpdateChangesFieldsAndFlushes(): void
    {
        $table = new QuoteTable();
        $table->setLabel('Old');
        $table->setDiscount(0);

        $this->entityManagerMock->expects($this->once())->method('flush');

        $updated = $this->service->update($table, 'Nowa', 15.5);

        $this->assertEquals('Nowa', $updated->getLabel());
        $this->assertEquals(15.5, $updated->getDiscount());
    }

    public function testDeleteRemovesAndFlushes(): void
    {
        $table = new QuoteTable();

        $this->entityManagerMock->expects($this->once())->method('remove')->with($table);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->service->delete($table);
        $this->assertTrue(true);
    }

    public function testFindByQuoteReturnsArray(): void
    {
        $expected = [new QuoteTable(), new QuoteTable()];
        $this->repositoryMock->expects($this->once())
            ->method('findBy')
            ->with(['quote' => 3])
            ->willReturn($expected);

        $result = $this->service->findByQuote(3);
        $this->assertSame($expected, $result);
    }
}