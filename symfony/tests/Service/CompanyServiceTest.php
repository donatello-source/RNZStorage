<?php

namespace App\Tests\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Service\CompanyService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyServiceTest extends TestCase
{
    private CompanyService $companyService;
    private $companyRepositoryMock;
    private $entityManagerMock;

    protected function setUp(): void
    {
        $this->companyRepositoryMock = $this->createMock(CompanyRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->companyService = new CompanyService(
            $this->companyRepositoryMock,
            $this->entityManagerMock
        );
    }

    public function testGetAllReturnsCompanies(): void
    {
        $companies = [new Company(), new Company()];
        $this->companyRepositoryMock
            ->method('findAll')
            ->willReturn($companies);

        $result = $this->companyService->getAll();

        $this->assertSame($companies, $result);
    }

    public function testGetByIdReturnsCompany(): void
    {
        $company = new Company();
        $this->companyRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($company);

        $result = $this->companyService->getById(1);
        $this->assertSame($company, $result);
    }

    public function testCreatePersistsCompany(): void
    {
        $data = [
            'nazwa' => 'ACME Inc.',
            'nip' => '1234567890',
            'adres' => 'Main Street 1',
            'telefon' => '123-456-789'
        ];

        $this->entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Company::class));

        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $company = $this->companyService->create($data);

        $this->assertInstanceOf(Company::class, $company);
        $this->assertEquals('ACME Inc.', $company->getNazwa());
        $this->assertEquals('1234567890', $company->getNip());
        $this->assertEquals('Main Street 1', $company->getAdres());
        $this->assertEquals('123-456-789', $company->getTelefon());
    }

    public function testDeleteExistingCompany(): void
    {
        $company = new Company();
        $this->companyRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($company);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('remove')
            ->with($company);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        $this->companyService->delete(1);
    }

    public function testDeleteThrowsExceptionWhenCompanyNotFound(): void
    {
        $this->companyRepositoryMock
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->companyService->delete(999);
    }
}
