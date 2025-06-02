<?php

namespace App\Tests\Controller;

use App\Entity\Person;
use App\Tests\AuthenticatedWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends AuthenticatedWebTestCase
{
    public function testListUsersAsAdmin(): void
    {
        $this->setTestUserRoles(['ROLE_ADMIN']);
        $this->logInSession();

        $this->client->request('GET', '/api/admin/users');
        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
    }

    public function testListUsersAsNonAdmin(): void
    {
        $this->setTestUserRoles(['ROLE_USER']);
        $this->logInSession();

        $this->client->request('GET', '/api/admin/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateUserRolesAsAdmin(): void
    {
        $this->setTestUserRoles(['ROLE_ADMIN']);
        $this->logInSession();

        // Dodaj drugiego użytkownika
        $user = $this->createTestPerson('otheruser@example.com', 'password', ['ROLE_USER']);

        $data = ['roles' => 'ROLE_ADMIN'];
        $this->client->request(
            'PUT',
            '/api/admin/users/' . $user->getId() . '/roles',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Role zaktualizowane', $response['message']);
    }

    public function testUpdateUserRolesAsNonAdmin(): void
    {
        $this->setTestUserRoles(['ROLE_USER']);
        $this->logInSession();

        $user = $this->createTestPerson('otheruser2@example.com', 'password', ['ROLE_USER']);

        $data = ['roles' => 'ROLE_ADMIN'];
        $this->client->request(
            'PUT',
            '/api/admin/users/' . $user->getId() . '/roles',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateUserRolesWithInvalidRole(): void
    {
        $this->setTestUserRoles(['ROLE_ADMIN']);
        $this->logInSession();

        $user = $this->createTestPerson('otheruser3@example.com', 'password', ['ROLE_USER']);

        $data = ['roles' => 123];
        $this->client->request(
            'PUT',
            '/api/admin/users/' . $user->getId() . '/roles',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $status = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(
            in_array($status, [Response::HTTP_BAD_REQUEST, Response::HTTP_FORBIDDEN]),
            "Expected 400 or 403, got $status"
        );
    }

    public function testUpdateUserRolesForNonExistingUser(): void
    {
        $this->setTestUserRoles(['ROLE_ADMIN']);
        $this->logInSession();

        $data = ['roles' => 'ROLE_ADMIN'];
        $this->client->request(
            'PUT',
            '/api/admin/users/99999/roles',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function setTestUserRoles(array $roles): void
    {
        $user = $this->entityManager->getRepository(Person::class)->findOneBy(['mail' => 'test@example.com']);
        $user->setRoles($roles);
        $this->entityManager->flush();
    }

    private function createTestPerson(string $email, string $plainPassword, array $roles): Person
    {
        $person = new Person();
        $person->setImie('Test');
        $person->setNazwisko('User');
        $person->setMail($email);
        $person->setStanowisko('Tester');
        $passwordHasher = self::getContainer()->get(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class);
        $person->setHaslo($passwordHasher->hashPassword($person, $plainPassword));
        $person->setRoles($roles);
        $this->entityManager->persist($person);
        $this->entityManager->flush();
        return $person;
    }
}