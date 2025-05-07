<?php

namespace App\Security;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;



class PersonAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly PersonRepository $personRepository
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/api/person/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['mail'] ?? '';
        $password = $data['haslo'] ?? '';

        return new Passport(
            new UserBadge($email, function ($userIdentifier) {
                $person = $this->personRepository->findOneBy(['mail' => $userIdentifier]);
        
                if (!$person) {
                    throw new UserNotFoundException();
                }
                if (in_array('ROLE_UNVERIFIED', $person->getRoles(), true)) {
                    throw new AuthenticationException('Twoje konto nie zostało jeszcze zweryfikowane.');
                }
                return $person;
            }),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var Person $user */
        $user = $token->getUser();

        return new JsonResponse([
            'message' => 'Zalogowano pomyślnie',
            'data' => [
                'id' => $user->getId(),
                'imie' => $user->getImie(),
                'nazwisko' => $user->getNazwisko(),
                'mail' => $user->getMail(),
                'role' => $user->getRoles(),
            ],
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => 'Błąd logowania: ' . $exception->getMessage()], 401);
    }
}
