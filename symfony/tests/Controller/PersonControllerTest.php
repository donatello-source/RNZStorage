<?php

namespace App\Tests\Controller;

use App\Entity\Person;
use App\Tests\AuthenticatedWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PersonControllerTest extends AuthenticatedWebTestCase
{
    public function testGetAllPersons(): void
    {
        $this->logInSession();
        $this->client->request('GET', '/api/person');
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse($this->client->getResponse(), 200);
        $this->assertIsArray(json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testGetPersonById(): void
    {
        $this->logInSession();
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

        $this->client->request(
            'POST',
            '/api/person/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

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
        $person->setRoles(['ROLE_USER']);

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
