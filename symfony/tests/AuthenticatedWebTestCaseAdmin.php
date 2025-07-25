<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

abstract class AuthenticatedWebTestCaseAdmin extends WebTestCase
{
    protected $client;
    protected $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);

        $repo = $this->entityManager->getRepository(\App\Entity\Person::class);
        $user = $repo->findOneBy(['mail' => 'admin@example.com']);
        if (!$user) {
            $user = new \App\Entity\Person();
            $user->setImie('admin');
            $user->setNazwisko('User');
            $user->setMail('admin@example.com');
            $user->setStanowisko('admin');
            $user->setRoles(['ROLE_ADMIN']);
            $passwordHasher = self::getContainer()->get(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class);
            $user->setHaslo($passwordHasher->hashPassword($user, 'testpassword'));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    protected function logInSession(): void
    {
        $this->client->request(
            'POST',
            '/api/person/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'mail' => 'admin@example.com',
                'haslo' => 'testpassword'
            ])
        );
    }
}