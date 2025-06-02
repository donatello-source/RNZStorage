<?php

namespace App\Tests\Controller;

use App\Entity\Company;
use App\Entity\Quote;
use App\Entity\Equipment;
use App\Entity\Category;
use App\Tests\AuthenticatedWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class QuotationControllerTest extends AuthenticatedWebTestCase
{
    public function testGetAllQuotations(): void
    {
        $this->logInSession();
        $this->client->request('GET', '/api/quotation');
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);
        $this->assertIsArray(json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testGetQuotationById(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $this->client->request('GET', '/api/quotation/' . $quote->getId());
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);

        $this->client->request('GET', '/api/quotation/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAddQuotation(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();

        $category = new Category();
        $category->setNazwa('Monitory');
        $this->entityManager->persist($category);

        $equipment = new Equipment();
        $equipment->setName('Monitor Dell');
        $equipment->setCategoryId($category->getId());
        $equipment->setPrice(1000);
        $equipment->setQuantity(10);
        $equipment->setDescription('Testowy monitor');
        $this->entityManager->persist($equipment);

        $this->entityManager->flush();

        $data = [
            'company_id' => $company->getId(),
            'projekt' => 'Test Projekt',
            'lokalizacja' => 'Warszawa',
            'rabatCalkowity' => 5,
            'daty' => [
                [
                    'type' => 'single',
                    'value' => '2025-06-01',
                    'comment' => 'Montaż'
                ]
            ],
            'tabele' => [
                [
                    'kategoria' => 'Sala A',
                    'rabatTabelki' => 0,
                    'sprzety' => [
                        [
                            'id' => $equipment->getId(),
                            'ilosc' => 2,
                            'dni' => 1,
                            'rabat' => 0,
                            'showComment' => false
                        ]
                    ]
                ]
            ]
        ];

        $this->client->request(
            'POST',
            '/api/quotation',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Błędne dane (brak wymaganych pól)
        $this->client->request(
            'POST',
            '/api/quotation',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testDeleteQuotation(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $this->client->request('DELETE', '/api/quotation/' . $quote->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->request('DELETE', '/api/quotation/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateQuotationStatus(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $this->client->request(
            'PATCH',
            '/api/quotation/' . $quote->getId() . '/status',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'przyjęta'])
        );
        $this->assertResponseIsSuccessful();

        // Brak statusu
        $this->client->request(
            'PATCH',
            '/api/quotation/' . $quote->getId() . '/status',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        // Nieistniejąca wycena
        $this->client->request(
            'PATCH',
            '/api/quotation/99999/status',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'przyjęta'])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateQuotation(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        // Dodaj kategorię i sprzęt do bazy
        $category = new Category();
        $category->setNazwa('Monitory');
        $this->entityManager->persist($category);

        $equipment = new Equipment();
        $equipment->setName('Monitor Dell');
        $equipment->setCategoryId($category->getId());
        $equipment->setPrice(1000);
        $equipment->setQuantity(10);
        $equipment->setDescription('Testowy monitor');
        $this->entityManager->persist($equipment);

        $this->entityManager->flush();

        $data = [
            'company_id' => $company->getId(),
            'projekt' => 'Test Projekt',
            'lokalizacja' => 'Warszawa',
            'rabatCalkowity' => 5,
            'daty' => [
                [
                    'type' => 'single',
                    'value' => '2025-06-01',
                    'comment' => 'Montaż'
                ]
            ],
            'tabele' => [
                [
                    'kategoria' => 'Sala A',
                    'rabatTabelki' => 0,
                    'sprzety' => [
                        [
                            'id' => $equipment->getId(), // użyj ID utworzonego sprzętu
                            'ilosc' => 2,
                            'dni' => 1,
                            'rabat' => 0,
                            'showComment' => false
                        ]
                    ]
                ]
            ]
        ];

        $this->client->request(
            'PATCH',
            '/api/quotation/' . $quote->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseIsSuccessful();

        // Nieistniejąca wycena
        $this->client->request(
            'PATCH',
            '/api/quotation/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function createTestCompany(): Company
    {
        $company = new Company();
        $company->setNazwa('Firma Testowa');
        $company->setNip('1234567890');
        $company->setAdres('ul. Testowa 1, Warszawa');
        $company->setTelefon('123-456-789');
        $this->entityManager->persist($company);
        $this->entityManager->flush();
        $this->entityManager->refresh($company);
        $this->assertNotNull($company->getId(), 'Company ID nie może być null');
        return $company;
    }

    private function createTestQuote(Company $company): Quote
    {
        $quote = new Quote();
        $quote->setCompany($company);
        $quote->setProjekt('Projekt Testowy');
        $quote->setLokalizacja('Warszawa');
        $quote->setStatus('nowa');
        $quote->setDataWystawienia(new \DateTime());
        $quote->setGlobalDiscount(10);
        $this->entityManager->persist($quote);
        $this->entityManager->flush();
        return $quote;
    }

    private function assertJsonResponse($response, int $statusCode): void
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            'Brak nagłówka Content-Type: application/json'
        );
    }
}
