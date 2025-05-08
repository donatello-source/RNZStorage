<?php

namespace App\Controller;

use App\Repository\PersonRepository;
use App\Entity\Person;
use App\Service\PersonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController
{
    #[Route('/api/admin/users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listUsers(PersonRepository $personRepository): JsonResponse
    {
        $users = $personRepository->findAll();
        $data = array_map(fn($u) => [
            'id' => $u->getId(),
            'imie' => $u->getImie(),
            'nazwisko' => $u->getNazwisko(),
            'mail' => $u->getMail(),
            'roles' => $u->getRoles()
        ], $users);

        return new JsonResponse(['data' => $data]);
    }

    #[Route('/api/admin/users/{id}/roles', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateUserRoles(int $id, Request $request, EntityManagerInterface $em, PersonRepository $repo): JsonResponse
    {
        $user = $repo->find($id);
        if (!$user) return new JsonResponse(['error' => 'Użytkownik nie znaleziony'], 404);

        $content = json_decode($request->getContent(), true);
        if (!isset($content['roles']) || !is_string($content['roles'])) {
            return new JsonResponse(['error' => 'Nieprawidłowa rola'], 400);
        }
        
        $user->setRoles([$content['roles']]);
        $em->flush();

        return new JsonResponse(['message' => 'Role zaktualizowane']);
    }
}
