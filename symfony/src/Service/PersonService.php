<?php

namespace App\Service;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PersonService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PersonRepository $personRepository
    ) {}

    public function getAll(): array
    {
        return $this->personRepository->findAll();
    }

    public function getById(int $id): ?Person
    {
        return $this->personRepository->find($id);
    }

    public function create(array $data): ?Person
    {
        if (!isset($data['imie'], $data['nazwisko'], $data['mail'], $data['haslo'])) {
            throw new BadRequestHttpException('Brak wymaganych pól');
        }

        $person = new Person();
        $person->setImie($data['imie']);
        $person->setNazwisko($data['nazwisko']);
        $person->setMail($data['mail']);
        $person->setHaslo(password_hash($data['haslo'], PASSWORD_BCRYPT));
        $person->setStanowisko("brak autoryzacji");

        $this->entityManager->persist($person);
        $this->entityManager->flush();

        return $person;
    }

}
