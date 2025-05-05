<?php

namespace App\Tests\Service;

use App\Entity\Person;
use App\Repository\PersonRepository;
use App\Service\PersonService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PersonServiceTest extends TestCase
{
    private $entityManagerMock;
    private $personRepositoryMock;
    private PersonService $personService;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->personRepositoryMock = $this->createMock(PersonRepository::class);
        $this->personService = new PersonService($this->entityManagerMock, $this->personRepositoryMock);
    }

    public function testGetAllReturnsArray(): void
    {
        $this->personRepositoryMock
            ->method('findAll')
            ->willReturn([]);

        $result = $this->personService->getAll();
        $this->assertIsArray($result);
    }

    public function testGetByIdReturnsPerson(): void
    {
        $person = new Person();
        $this->personRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($person);

        $result = $this->personService->getById(1);
        $this->assertSame($person, $result);
    }

    public function testCreateThrowsExceptionWhenMissingFields(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->personService->create(['imie' => 'Jan']); // za mało danych
    }

    public function testCreateReturnsPerson(): void
    {
        $data = [
            'imie' => 'Jan',
            'nazwisko' => 'Kowalski',
            'mail' => 'jan@example.com',
            'haslo' => 'tajnehaslo'
        ];

        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');

        $person = $this->personService->create($data);
        $this->assertInstanceOf(Person::class, $person);
        $this->assertEquals('jan@example.com', $person->getMail());
        $this->assertTrue(password_verify('tajnehaslo', $person->getHaslo()));
    }

    public function testLoginSuccess(): void
    {
        $person = new Person();
        $person->setMail('jan@example.com');
        $person->setHaslo(password_hash('tajnehaslo', PASSWORD_BCRYPT));

        $this->personRepositoryMock
            ->method('findOneBy')
            ->with(['mail' => 'jan@example.com'])
            ->willReturn($person);

        $result = $this->personService->login([
            'mail' => 'jan@example.com',
            'haslo' => 'tajnehaslo'
        ]);

        $this->assertSame($person, $result);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $person = new Person();
        $person->setMail('jan@example.com');
        $person->setHaslo(password_hash('prawidlowehaslo', PASSWORD_BCRYPT));

        $this->personRepositoryMock
            ->method('findOneBy')
            ->willReturn($person);

        $this->expectException(AuthenticationException::class);

        $this->personService->login([
            'mail' => 'jan@example.com',
            'haslo' => 'blednehaslo'
        ]);
    }

    public function testLoginFailsWithMissingData(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->personService->login(['mail' => 'brakhasla@example.com']);
    }
}
