<?php

namespace App\Tests\Service;

use App\Entity\Quote;
use App\Entity\QuoteDate;
use App\Service\QuoteDateService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class QuoteDateServiceTest extends TestCase
{
    private $entityManagerMock;
    private $repositoryMock;
    private QuoteDateService $service;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->repositoryMock = $this->createMock(EntityRepository::class);

        $this->entityManagerMock->method('getRepository')
            ->with(QuoteDate::class)
            ->willReturn($this->repositoryMock);

        $this->service = new QuoteDateService($this->entityManagerMock);
    }

    public function testCreatePersistsAndFlushesQuoteDate(): void
    {
        $quote = new Quote();
        $type = 'single';
        $value = '2024-06-01';
        $comment = 'test';

        $this->entityManagerMock->expects($this->once())->method('persist')->with($this->isInstanceOf(QuoteDate::class));
        $this->entityManagerMock->expects($this->once())->method('flush');

        $date = $this->service->create($quote, $type, $value, $comment);

        $this->assertInstanceOf(QuoteDate::class, $date);
        $this->assertSame($quote, $date->getQuote());
        $this->assertEquals($type, $date->getType());
        $this->assertEquals($value, $date->getValue());
        $this->assertEquals($comment, $date->getComment());
    }

    public function testFindReturnsQuoteDate(): void
    {
        $quoteDate = new QuoteDate();
        $this->repositoryMock->expects($this->once())->method('find')->with(1)->willReturn($quoteDate);

        $result = $this->service->find(1);
        $this->assertSame($quoteDate, $result);
    }

    public function testUpdateChangesFieldsAndFlushes(): void
    {
        $date = new QuoteDate();
        $date->setType('old');
        $date->setValue('2024-01-01');
        $date->setComment('old');

        $this->entityManagerMock->expects($this->once())->method('flush');

        $updated = $this->service->update($date, 'new', '2024-06-01', 'nowy');
        $this->assertEquals('new', $updated->getType());
        $this->assertEquals('2024-06-01', $updated->getValue());
        $this->assertEquals('nowy', $updated->getComment());
    }

    public function testDeleteRemovesAndFlushes(): void
    {
        $date = new QuoteDate();

        $this->entityManagerMock->expects($this->once())->method('remove')->with($date);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->service->delete($date);
        $this->assertTrue(true);
    }

    public function testFindByQuoteReturnsArray(): void
    {
        $expected = [new QuoteDate(), new QuoteDate()];
        $this->repositoryMock->expects($this->once())
            ->method('findBy')
            ->with(['quote' => 5])
            ->willReturn($expected);

        $result = $this->service->findByQuote(5);
        $this->assertSame($expected, $result);
    }
}