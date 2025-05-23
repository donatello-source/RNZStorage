<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\CompanyService;


use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/api/company')]
final class CompanyController extends AbstractController
{
    public function __construct(private readonly CompanyService $companyService) {}
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
    public function companyAll(): JsonResponse
    {
        return $this->json($this->companyService->getAll());
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
    public function getCompanyById(int $id): JsonResponse
    {
        $company = $this->companyService->getById($id);
        if (!$company) {
            throw new NotFoundHttpException('Company not found');
        }
        return $this->json($company);
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
    public function addCompany(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['nazwa'], $data['nip'], $data['adres'])) {
            throw new BadRequestHttpException('Missing required fields');
        }
        $company = $this->companyService->create($data);
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
    public function deleteCompany(int $id): JsonResponse
    {
        $this->companyService->delete($id);
        return $this->json(['message' => 'Company deleted successfully']);
    }
    
}
