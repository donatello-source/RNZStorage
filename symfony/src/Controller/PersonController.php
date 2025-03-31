<?php

namespace App\Controller;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/person')]
final class PersonController extends AbstractController
{
    #[Route('', name: 'person_all', methods: ['GET'])]
    public function personAll(PersonRepository $personRepository): JsonResponse
    {
        $persons = $personRepository->findAll();
        return $this->json($persons, 200);
    }

    #[Route('/{id}', name: 'get_person_by_id', methods: ['GET'])]
    public function getPersonById(int $id, PersonRepository $personRepository): JsonResponse
    {
        $person = $personRepository->find($id);
        if (!$person) {
            return $this->json(['error' => 'Person not found'], 404);
        }
        return $this->json($person, 200);
    }

    #[Route('', name: 'add_person', methods: ['POST'])]
    public function addPerson(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['imie'], $data['nazwisko'], $data['mail'], $data['haslo'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $person = new Person();
        $person->setImie($data['imie']);
        $person->setNazwisko($data['nazwisko']);
        $person->setMail($data['mail']);
        $person->setHaslo(password_hash($data['haslo'], PASSWORD_BCRYPT));

        $entityManager->persist($person);
        $entityManager->flush();

        return $this->json(['message' => 'Dodano nową osobę', 'data' => $person], 201);
    }
}
