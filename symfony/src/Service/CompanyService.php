<?php

namespace App\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class CompanyService
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function getAll(): array
    {
        return $this->companyRepository->findAll();
    }

    public function getById(int $id): ?Company
    {
        return $this->companyRepository->find($id);
    }

    public function create(array $data): Company
    {
        $company = new Company();
        $company->setNazwa($data['nazwa']);
        $company->setNip($data['nip']);
        $company->setAdres($data['adres']);
        $company->setTelefon($data['telefon'] ?? null);

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return $company;
    }

    public function delete(int $id): bool
    {
        $company = $this->companyRepository->find($id);
        if (!$company) {
            return false;
        }

        $this->entityManager->remove($company);
        $this->entityManager->flush();
        return true;
    }
}
