<?php

namespace App\Tests\Controller;

use App\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class PersonControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    public function testGetAllPersons(): void
    {
        $this->client->request('GET', '/api/person');

        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);
    }

    public function testGetPersonById(): void
    {
        $person = $this->createTestPerson();

        $this->client->request('GET', '/api/person/' . $person->getId());
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);

        $this->client->request('GET', '/api/person/99999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAddPerson(): void
    {
        $data = [
            'imie' => 'Anna',
            'nazwisko' => 'Nowak',
            'mail' => 'anna.nowak@example.com',
            'haslo' => 'testowehaslo123'
        ];

        $this->client->request(
            'POST',
            '/api/person',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponse($this->client->getResponse(), 201);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Dodano nową osobę', $response['message']);

        // Brak wymaganych danych
        $this->client->request(
            'POST',
            '/api/person',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testLoginPerson(): void
    {
        $person = $this->createTestPerson('login@test.com', 'haslotest');

        // Prawidłowe dane logowania
        $this->client->request(
            'POST',
            '/api/person/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['mail' => 'login@test.com', 'haslo' => 'haslotest'])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponse($this->client->getResponse(), 200);

        // Brak danych
        $this->client->request(
            'POST',
            '/api/person/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        // Nieprawidłowe dane logowania
        $this->client->request(
            'POST',
            '/api/person/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['mail' => 'login@test.com', 'haslo' => 'zlehaslo'])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    private function createTestPerson(string $email = 'jan.kowalski@example.com', string $plainPassword = 'haslo123'): Person
    {
        $person = new Person();
        $person->setImie('Jan');
        $person->setNazwisko('Kowalski');
        $person->setMail($email);
        $person->setHaslo(password_hash($plainPassword, PASSWORD_BCRYPT));
        $person->setStanowisko('Tester');

        $this->entityManager->persist($person);
        $this->entityManager->flush();

        return $person;
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
