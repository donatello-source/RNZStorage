<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/company')]
final class CompanyController extends AbstractController
{
    #[Route('', name: 'company_all', methods: ['GET'])]
    public function companyAll(CompanyRepository $companyRepository): JsonResponse
    {
        $companies = $companyRepository->findAll();
        return $this->json($companies, 200);
    }

    #[Route('/{id}', name: 'get_company_by_id', methods: ['GET'])]
    public function getCompanyById(int $id, CompanyRepository $companyRepository): JsonResponse
    {
        $company = $companyRepository->find($id);
        if (!$company) {
            return $this->json(['error' => 'Company not found'], 404);
        }
        return $this->json($company, 200);
    }

    #[Route('', name: 'add_company', methods: ['POST'])]
    public function addCompany(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nazwa'], $data['nip'], $data['adres'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $company = new Company();
        $company->setName($data['nazwa']);
        $company->setNip($data['nip']);
        $company->setAdres($data['adres']);
        $company->setTelefon($data['telefon'] ?? null);

        $entityManager->persist($company);
        $entityManager->flush();

        return $this->json(['message' => 'Company added successfully', 'data' => $company], 201);
    }

    #[Route('/{id}', name: 'delete_company', methods: ['DELETE'])]
    public function deleteCompany(int $id, CompanyRepository $companyRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $company = $companyRepository->find($id);

        if (!$company) {
            return $this->json(['error' => 'Company not found'], 404);
        }

        $entityManager->remove($company);
        $entityManager->flush();

        return $this->json(['message' => 'Company deleted successfully'], 200);
    }


}
