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

}
