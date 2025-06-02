<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Equipment;
use App\Tests\AuthenticatedWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EquipmentControllerTest extends AuthenticatedWebTestCase
{
    public function testGetAllEquipment(): void
    {
        $this->logInSession();
        $this->client->request('GET', '/api/equipment');
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);
        $this->assertIsArray(json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testAddEquipment(): void
    {
        $this->logInSession();
        $category = new Category();
        $category->setNazwa('Monitory');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $data = [
            'name' => 'Monitor Dell',
            'description' => '24 cale',
            'quantity' => 3,
            'price' => 599.99,
            'categoryid' => $category->getId()
        ];

        $this->client->request('POST', '/api/equipment', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponse($this->client->getResponse(), 201);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Monitor Dell', $response['data']['name']);

        $this->client->request('POST', '/api/equipment', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetEquipmentById(): void
    {
        $this->logInSession();
        $equipment = $this->createEquipment('Laptop HP');

        $this->client->request('GET', '/api/equipment/' . $equipment->getId());
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);

        $this->client->request('GET', '/api/equipment/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetEquipmentByCategory(): void
    {
        $this->logInSession();
        $category = new Category();
        $category->setNazwa('Drukarki');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $equipment = new Equipment();
        $equipment->setName('HP DeskJet');
        $equipment->setQuantity(5);
        $equipment->setPrice(299.99);
        $equipment->setCategoryId($category->getId());
        $this->entityManager->persist($equipment);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/equipment/category/' . $category->getId());
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);

        $this->client->request('GET', '/api/equipment/category/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testEditEquipment(): void
    {
        $this->logInSession();
        $equipment = $this->createEquipment('Do edycji');

        $data = ['name' => 'Zmieniony sprzęt', 'quantity' => 7];
        $this->client->request('PUT', '/api/equipment/' . $equipment->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponse($this->client->getResponse(), 200);

        $this->client->request('PUT', '/api/equipment/99999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteEquipment(): void
    {
        $this->logInSession();
        $equipment = $this->createEquipment('Do usunięcia');

        $this->client->request('DELETE', '/api/equipment/' . $equipment->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->request('DELETE', '/api/equipment/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function createEquipment(string $name): Equipment
    {
        $category = new Category();
        $category->setNazwa('Testowa Kategoria');
        $this->entityManager->persist($category);

        $equipment = new Equipment();
        $equipment->setName($name);
        $equipment->setQuantity(1);
        $equipment->setPrice(123.45);
        $equipment->setCategoryId($category->getId());

        $this->entityManager->persist($equipment);
        $this->entityManager->flush();

        return $equipment;
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
