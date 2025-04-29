<?php

namespace App\Tests\Controller;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class CompanyControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    public function testGetAllCompanies(): void
    {
        $this->client->request('GET', '/api/company');
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testGetCompanyById(): void
    {
        // Add test company
        $company = new Company();
        $company->setNazwa('Test Co');
        $company->setNip('1234567890');
        $company->setAdres('Test Address');
        $company->setTelefon('123-456-789');
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/company/' . $company->getId());
        $this->assertResponseIsSuccessful();

        // 404 Not Found
        $this->client->request('GET', '/api/company/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAddCompany(): void
    {
        $validData = [
            'nazwa' => 'New Company',
            'nip' => '9876543210',
            'adres' => 'ul. Testowa 2, Kraków',
            'telefon' => '987-654-321'
        ];

        $this->client->request('POST', '/api/company', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($validData));
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponse($this->client->getResponse(), 201);

        $invalidData = ['nip' => '111']; // Missing required fields
        $this->client->request('POST', '/api/company', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($invalidData));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testDeleteCompany(): void
    {
        $company = new Company();
        $company->setNazwa('To Delete');
        $company->setNip('1122334455');
        $company->setAdres('Delete St.');
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/company/' . $company->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->request('DELETE', '/api/company/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
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
