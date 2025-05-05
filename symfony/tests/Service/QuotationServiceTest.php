<?php

namespace App\Tests\Service;

use App\Entity\Quote;
use App\Repository\QuoteRepository;
use App\Repository\QuoteEquipmentRepository;
use App\Service\QuotationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuotationServiceTest extends TestCase
{
    private $quoteRepositoryMock;
    private $quoteEquipmentRepositoryMock;
    private $entityManagerMock;
    private QuotationService $quotationService;

    protected function setUp(): void
    {
        $this->quoteRepositoryMock = $this->createMock(QuoteRepository::class);
        $this->quoteEquipmentRepositoryMock = $this->createMock(QuoteEquipmentRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->quotationService = new QuotationService(
            $this->quoteRepositoryMock,
            $this->quoteEquipmentRepositoryMock,
            $this->entityManagerMock
        );
    }

    public function testGetAllQuotesReturnsArray(): void
    {
        $this->quoteRepositoryMock
            ->method('findAll')
            ->willReturn([]);

        $this->assertIsArray($this->quotationService->getAllQuotes());
    }

    public function testGetQuoteByIdReturnsQuote(): void
    {
        $quote = new Quote();
        $this->quoteRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($quote);

        $this->assertSame($quote, $this->quotationService->getQuoteById(1));
    }

    public function testGetQuoteByIdThrowsExceptionIfNotFound(): void
    {
        $this->quoteRepositoryMock
            ->method('find')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->quotationService->getQuoteById(999);
    }

    public function testAddQuoteSuccessfullyCreatesQuote(): void
    {
        $data = [
            'company' => 1,
            'status' => 'nowa',
            'dane_kontaktowe' => 'Jan Nowak',
            'miejsce' => 'Warszawa',
            'data_wystawienia' => '2024-05-01',
            'data_poczatek' => '2024-05-10',
            'data_koniec' => '2024-05-20',
        ];

        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');

        $quote = $this->quotationService->addQuote($data);

        $this->assertInstanceOf(Quote::class, $quote);
        $this->assertEquals(1, $quote->getCompany());
    }

    public function testAddQuoteThrowsExceptionOnMissingData(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->quotationService->addQuote([
            'company' => 1
        ]);
    }

    public function testAddQuoteThrowsExceptionOnInvalidDate(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->quotationService->addQuote([
            'company' => 1,
            'status' => 'nowa',
            'dane_kontaktowe' => 'Jan Nowak',
            'miejsce' => 'Warszawa',
            'data_wystawienia' => 'invalid-date',
            'data_poczatek' => '2024-05-10',
            'data_koniec' => '2024-05-20',
        ]);
    }

    public function testDeleteQuoteRemovesEntity(): void
    {
        $quote = new Quote();

        $this->quoteRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($quote);

        $this->entityManagerMock->expects($this->once())->method('remove')->with($quote);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->quotationService->deleteQuote(1);
        $this->assertTrue(true);
    }

    public function testDeleteQuoteThrowsExceptionIfNotFound(): void
    {
        $this->quoteRepositoryMock
            ->method('find')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->quotationService->deleteQuote(999);
    }
}
