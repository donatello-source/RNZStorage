<?php

namespace App\Tests\Controller;

use App\Entity\Company;
use App\Entity\Quote;
use App\Entity\Equipment;
use App\Entity\Category;
use App\Tests\AuthenticatedWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class QuoteDateControllerTest extends AuthenticatedWebTestCase
{
    public function testAddQuoteDate(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $data = [
            'quote_id' => $quote->getId(),
            'type' => 'single',
            'value' => '2025-06-01',
            'comment' => 'Montaż'
        ];

        $this->client->request(
            'POST',
            '/api/quote-date/create',
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
            '/api/quote-date/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetQuoteDateById(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $dateData = [
            'quote_id' => $quote->getId(),
            'type' => 'single',
            'value' => '2025-06-01',
            'comment' => 'Montaż'
        ];
        $this->client->request(
            'POST',
            '/api/quote-date/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($dateData)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $dateId = $response['id'];

        $this->client->request('GET', '/api/quote-date/' . $dateId);
        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('single', $response['type']);

        $this->client->request('GET', '/api/quote-date/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateQuoteDate(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $dateData = [
            'quote_id' => $quote->getId(),
            'type' => 'single',
            'value' => '2025-06-01',
            'comment' => 'Montaż'
        ];
        $this->client->request(
            'POST',
            '/api/quote-date/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($dateData)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $dateId = $response['id'];

        $updateData = [
            'type' => 'range',
            'value' => '2025-06-01 - 2025-06-03',
            'comment' => 'Realizacja'
        ];
        $this->client->request(
            'PUT',
            '/api/quote-date/' . $dateId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        $this->assertResponseIsSuccessful();

        $this->client->request(
            'PUT',
            '/api/quote-date/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteQuoteDate(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        $dateData = [
            'quote_id' => $quote->getId(),
            'type' => 'single',
            'value' => '2025-06-01',
            'comment' => 'Montaż'
        ];
        $this->client->request(
            'POST',
            '/api/quote-date/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($dateData)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $dateId = $response['id'];

        $this->client->request('DELETE', '/api/quote-date/' . $dateId);
        $this->assertResponseIsSuccessful();

        $this->client->request('DELETE', '/api/quote-date/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testListQuoteDatesForQuote(): void
    {
        $this->logInSession();
        $company = $this->createTestCompany();
        $quote = $this->createTestQuote($company);

        foreach ([['single', '2025-06-01'], ['range', '2025-06-01 - 2025-06-03']] as [$type, $value]) {
            $dateData = [
                'quote_id' => $quote->getId(),
                'type' => $type,
                'value' => $value,
                'comment' => null
            ];
            $this->client->request(
                'POST',
                '/api/quote-date/create',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($dateData)
            );
            $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        }

        $this->client->request('GET', '/api/quote-date/list/' . $quote->getId());
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
        $quote->setGlobalDiscount(10);
        $this->entityManager->persist($quote);
        $this->entityManager->flush();
        return $quote;
    }
}