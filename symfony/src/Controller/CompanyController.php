<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/company')]
final class CompanyController extends AbstractController
{
    #[Route('', name: 'company_all', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lista wszystkich firm',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca wszystkie firmy',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nazwa', type: 'string', example: 'Firma XYZ'),
                            new OA\Property(property: 'nip', type: 'string', example: '1234567890'),
                            new OA\Property(property: 'adres', type: 'string', example: 'ul. Przykładowa 1, Warszawa'),
                            new OA\Property(property: 'telefon', type: 'string', example: '123-456-789')
                        ]
                    )
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Firma')]
    public function companyAll(CompanyRepository $companyRepository): JsonResponse
    {
        $companies = $companyRepository->findAll();
        return $this->json($companies, 200);
    }

    #[Route('/{id}', name: 'get_company_by_id', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobiera firmę po ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID firmy',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Zwraca dane firmy',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'nazwa', type: 'string', example: 'Firma XYZ'),
                        new OA\Property(property: 'nip', type: 'string', example: '1234567890'),
                        new OA\Property(property: 'adres', type: 'string', example: 'ul. Przykładowa 1, Warszawa'),
                        new OA\Property(property: 'telefon', type: 'string', example: '123-456-789')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Firma nie znaleziona'
            )
        ]
    )]
    #[OA\Tag(name: 'Firma')]
    public function getCompanyById(int $id, CompanyRepository $companyRepository): JsonResponse
    {
        $company = $companyRepository->find($id);
        if (!$company) {
            return $this->json(['error' => 'Company not found'], 404);
        }
        return $this->json($company, 200);
    }

    #[Route('', name: 'add_company', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaje nową firmę',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nazwa', 'nip', 'adres'],
                properties: [
                    new OA\Property(property: 'nazwa', type: 'string', example: 'Firma XYZ'),
                    new OA\Property(property: 'nip', type: 'string', example: '1234567890'),
                    new OA\Property(property: 'adres', type: 'string', example: 'ul. Przykładowa 1, Warszawa'),
                    new OA\Property(property: 'telefon', type: 'string', example: '123-456-789')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Dodano firmę',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Company added successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 5),
                                new OA\Property(property: 'nazwa', type: 'string', example: 'Firma XYZ'),
                                new OA\Property(property: 'nip', type: 'string', example: '1234567890'),
                                new OA\Property(property: 'adres', type: 'string', example: 'ul. Przykładowa 1, Warszawa'),
                                new OA\Property(property: 'telefon', type: 'string', example: '123-456-789')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Brak wymaganych danych')
        ]
    )]
    #[OA\Tag(name: 'Firma')]
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
    #[OA\Delete(
        summary: 'Usuwa firmę po ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID firmy',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Firma została usunięta',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Company deleted successfully')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Firma nie znaleziona'
            )
        ]
    )]
    #[OA\Tag(name: 'Firma')]
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
