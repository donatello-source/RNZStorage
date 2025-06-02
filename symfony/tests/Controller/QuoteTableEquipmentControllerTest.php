<?php

namespace App\Tests\Controller;

use App\Entity\Company;
use App\Entity\Quote;
use App\Entity\Category;
use App\Entity\Equipment;
use App\Entity\QuoteTable;
use App\Tests\AuthenticatedWebTestCase;

class QuoteTableEquipmentControllerTest extends AuthenticatedWebTestCase
{
    public function testAddQuoteTableEquipment(): void
    {
        $this->logInSession();
        [$company, $quote, $table, $equipment] = $this->prepareEntities();

        $data = [
            'quote_table_id' => $table->getId(),
            'equipment_id' => $equipment->getId(),
            'count' => 2,
            'days' => 3,
            'discount' => 10,
            'show_comment' => true
        ];

        $this->client->request(
            'POST',
            '/api/quote-table-equipment/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);

        $data['quote_table_id'] = 99999;
        $this->client->request(
            'POST',
            '/api/quote-table-equipment/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetQuoteTableEquipmentById(): void
    {
        $this->logInSession();
        [$company, $quote, $table, $equipment] = $this->prepareEntities();

        // Dodaj sprzęt do tabelki
        $data = [
            'quote_table_id' => $table->getId(),
            'equipment_id' => $equipment->getId(),
            'count' => 2,
            'days' => 3,
            'discount' => 10,
            'show_comment' => true
        ];
        $this->client->request(
            'POST',
            '/api/quote-table-equipment/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $qteId = $response['id'];

        // Pobierz po ID
        $this->client->request('GET', '/api/quote-table-equipment/' . $qteId);
        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(2, $response['count']);

        // Nieistniejący sprzęt
        $this->client->request('GET', '/api/quote-table-equipment/99999');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateQuoteTableEquipment(): void
    {
        $this->logInSession();
        [$company, $quote, $table, $equipment] = $this->prepareEntities();

        // Dodaj sprzęt do tabelki
        $data = [
            'quote_table_id' => $table->getId(),
            'equipment_id' => $equipment->getId(),
            'count' => 2,
            'days' => 3,
            'discount' => 10,
            'show_comment' => true
        ];
        $this->client->request(
            'POST',
            '/api/quote-table-equipment/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $qteId = $response['id'];

        // Aktualizuj sprzęt
        $updateData = [
            'count' => 5,
            'days' => 1,
            'discount' => 0,
            'show_comment' => false
        ];
        $this->client->request(
            'PUT',
            '/api/quote-table-equipment/' . $qteId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        $this->assertResponseIsSuccessful();

        // Nieistniejący sprzęt
        $this->client->request(
            'PUT',
            '/api/quote-table-equipment/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteQuoteTableEquipment(): void
    {
        $this->logInSession();
        [$company, $quote, $table, $equipment] = $this->prepareEntities();

        // Dodaj sprzęt do tabelki
        $data = [
            'quote_table_id' => $table->getId(),
            'equipment_id' => $equipment->getId(),
            'count' => 2,
            'days' => 3,
            'discount' => 10,
            'show_comment' => true
        ];
        $this->client->request(
            'POST',
            '/api/quote-table-equipment/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $qteId = $response['id'];

        // Usuń sprzęt
        $this->client->request('DELETE', '/api/quote-table-equipment/' . $qteId);
        $this->assertResponseIsSuccessful();

        // Nieistniejący sprzęt
        $this->client->request('DELETE', '/api/quote-table-equipment/99999');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testListQuoteTableEquipmentsForTable(): void
    {
        $this->logInSession();
        [$company, $quote, $table, $equipment] = $this->prepareEntities();

        foreach ([['Monitor', 2], ['Laptop', 1]] as [$name, $count]) {
            $eq = new Equipment();
            $eq->setName($name);
            $eq->setCategoryId($equipment->getCategoryId());
            $eq->setPrice(1000);
            $eq->setQuantity(10);
            $eq->setDescription('Test');
            $this->entityManager->persist($eq);
            $this->entityManager->flush();

            $data = [
                'quote_table_id' => $table->getId(),
                'equipment_id' => $eq->getId(),
                'count' => $count,
                'days' => 1,
                'discount' => 0,
                'show_comment' => false
            ];
            $this->client->request(
                'POST',
                '/api/quote-table-equipment/create',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($data)
            );
            $this->assertResponseStatusCodeSame(201);
        }

        // Pobierz listę
        $this->client->request('GET', '/api/quote-table-equipment/list/' . $table->getId());
        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount(2, $response);
    }

    // Pomocnicza metoda do przygotowania encji
    private function prepareEntities(): array
    {
        $company = new Company();
        $company->setNazwa('Test Company');
        $company->setNip('1234567890');
        $company->setAdres('Testowa 1');
        $this->entityManager->persist($company);

        $quote = new Quote();
        $quote->setCompany($company);
        $quote->setProjekt('Projekt Testowy');
        $quote->setLokalizacja('Warszawa');
        $quote->setStatus('nowa');
        $quote->setDataWystawienia(new \DateTime());
        $quote->setGlobalDiscount(0);
        $this->entityManager->persist($quote);

        $category = new Category();
        $category->setNazwa('Monitory');
        $this->entityManager->persist($category);

        $equipment = new Equipment();
        $equipment->setName('Monitor Dell');
        $equipment->setCategoryId($category->getId());
        $equipment->setPrice(1230);
        $equipment->setQuantity(10);
        $equipment->setDescription('Testowy monitor');
        $this->entityManager->persist($equipment);

        $table = new QuoteTable();
        $table->setQuote($quote);
        $table->setLabel('Sala A');
        $table->setDiscount(0);
        $this->entityManager->persist($table);

        $this->entityManager->flush();

        return [$company, $quote, $table, $equipment];
    }
}