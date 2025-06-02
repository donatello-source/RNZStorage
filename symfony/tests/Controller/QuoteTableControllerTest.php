<?php

namespace App\Tests\Controller;

use App\Entity\Company;
use App\Entity\Quote;
use App\Tests\AuthenticatedWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class QuoteTableControllerTest extends AuthenticatedWebTestCase
{
    public function testAddQuoteTable(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $data = [
            'quote_id' => $quote->getId(),
            'label' => 'Sala A',
            'discount' => 5
        ];

        $this->client->request(
            'POST',
            '/api/quote-table/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);

        $data['quote_id'] = 99999;
        $this->client->request(
            'POST',
            '/api/quote-table/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetQuoteTableById(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $data = [
            'quote_id' => $quote->getId(),
            'label' => 'Sala A',
            'discount' => 5
        ];
        $this->client->request(
            'POST',
            '/api/quote-table/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $tableId = $response['id'];

        $this->client->request('GET', '/api/quote-table/' . $tableId);
        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Sala A', $response['label']);

        $this->client->request('GET', '/api/quote-table/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateQuoteTable(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $data = [
            'quote_id' => $quote->getId(),
            'label' => 'Sala A',
            'discount' => 5
        ];
        $this->client->request(
            'POST',
            '/api/quote-table/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $tableId = $response['id'];

        $updateData = [
            'label' => 'Sala B',
            'discount' => 10
        ];
        $this->client->request(
            'PUT',
            '/api/quote-table/' . $tableId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        $this->assertResponseIsSuccessful();

        $this->client->request(
            'PUT',
            '/api/quote-table/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteQuoteTable(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $data = [
            'quote_id' => $quote->getId(),
            'label' => 'Sala A',
            'discount' => 5
        ];
        $this->client->request(
            'POST',
            '/api/quote-table/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $tableId = $response['id'];

        $this->client->request('DELETE', '/api/quote-table/' . $tableId);
        $this->assertResponseIsSuccessful();

        $this->client->request('DELETE', '/api/quote-table/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testListQuoteTablesForQuote(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        foreach ([['Sala A', 5], ['Sala B', 10]] as [$label, $discount]) {
            $data = [
                'quote_id' => $quote->getId(),
                'label' => $label,
                'discount' => $discount
            ];
            $this->client->request(
                'POST',
                '/api/quote-table/create',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($data)
            );
            $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        }

        $this->client->request('GET', '/api/quote-table/list/' . $quote->getId());
        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount(2, $response);
    }

    private function createTestCompany(): Company
    {
        $company = new Company();
        $company->setNazwa('Test Company');
        $company->setNip('1234567890');
        $company->setAdres('Testowa 1');
        $this->entityManager->persist($company);
        $this->entityManager->flush();
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
        $quote->setGlobalDiscount(0);
        $this->entityManager->persist($quote);
        $this->entityManager->flush();
        return $quote;
    }
}