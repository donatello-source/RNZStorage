<?php

namespace App\Tests\Service;

use App\Entity\Quote;
use App\Entity\Company;
use App\Entity\Equipment;
use App\Entity\QuoteDate;
use App\Entity\QuoteTable;
use App\Entity\QuoteTableEquipment;
use App\Repository\QuoteRepository;
use App\Service\QuotationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuotationServiceTest extends TestCase
{
    private $quoteRepositoryMock;
    private $entityManagerMock;
    private QuotationService $quotationService;

    protected function setUp(): void
    {
        $this->quoteRepositoryMock = $this->createMock(QuoteRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->quotationService = new QuotationService(
            $this->quoteRepositoryMock,
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

    public function testAddQuoteThrowsExceptionOnMissingCompanyId(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->quotationService->addQuote([
            'projekt' => 'Test',
            'lokalizacja' => 'Warszawa'
        ]);
    }

    public function testAddQuoteThrowsExceptionOnCompanyNotFound(): void
    {
        $companyRepo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $companyRepo->method('find')->willReturn(null);

        $this->entityManagerMock->method('getRepository')
            ->willReturn($companyRepo);

        $this->expectException(BadRequestHttpException::class);
        $this->quotationService->addQuote([
            'company_id' => 123,
            'projekt' => 'Test',
            'lokalizacja' => 'Warszawa'
        ]);
    }

    public function testAddQuoteSuccessfullyCreatesQuote(): void
    {
        $company = new Company();
        $company->setNazwa('Firma');
        $company->setNip('1234567890');

        $equipment = new Equipment();
        $equipment->setName('Sprzęt');
        $equipment->setPrice(100);

        $companyRepo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $companyRepo->method('find')->willReturn($company);

        $equipmentRepo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $equipmentRepo->method('find')->willReturn($equipment);

        $this->entityManagerMock->method('getRepository')
            ->willReturnMap([
                [Company::class, $companyRepo],
                [Equipment::class, $equipmentRepo],
            ]);

        $this->entityManagerMock->expects($this->atLeastOnce())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->entityManagerMock->expects($this->once())->method('beginTransaction');
        $this->entityManagerMock->expects($this->once())->method('commit');

        $data = [
            'company_id' => 1,
            'projekt' => 'Test Projekt',
            'lokalizacja' => 'Warszawa',
            'rabatCalkowity' => 5,
            'daty' => [
                ['type' => 'single', 'value' => '2024-06-01', 'comment' => 'test']
            ],
            'tabele' => [
                [
                    'kategoria' => 'Sala A',
                    'rabatTabelki' => 10,
                    'sprzety' => [
                        [
                            'id' => 1,
                            'ilosc' => 2,
                            'dni' => 3,
                            'rabat' => 0,
                            'showComment' => true
                        ]
                    ]
                ]
            ]
        ];

        $quote = $this->quotationService->addQuote($data);

        $this->assertInstanceOf(Quote::class, $quote);
        $this->assertEquals($company, $quote->getCompany());
        $this->assertEquals('Test Projekt', $quote->getProjekt());
        $this->assertEquals('Warszawa', $quote->getLokalizacja());
        $this->assertEquals(5, $quote->getGlobalDiscount());
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
