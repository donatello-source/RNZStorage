<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $imie = null;

    #[ORM\Column(length: 50)]
    private ?string $nazwisko = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $mail = null;

    #[ORM\Column(length: 255)]
    private ?string $haslo = null;

    #[ORM\Column(length: 20)]
    private ?string $stanowisko = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getImie(): ?string
    {
        return $this->imie;
    }

    public function setImie(string $imie): static
    {
        $this->imie = $imie;

        return $this;
    }

    public function getNazwisko(): ?string
    {
        return $this->nazwisko;
    }

    public function setNazwisko(string $nazwisko): static
    {
        $this->nazwisko = $nazwisko;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getHaslo(): ?string
    {
        return $this->haslo;
    }

    public function setHaslo(string $haslo): static
    {
        $this->haslo = $haslo;

        return $this;
    }

    public function getStanowisko(): ?string
    {
        return $this->stanowisko;
    }

    public function setStanowisko(string $stanowisko): static
    {
        $this->stanowisko = $stanowisko;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->mail;
    }

    public function getPassword(): string
    {
        return $this->haslo;
    }

    public function getRoles(): array
    {
        return match ($this->stanowisko) {
            'ROLE_ADMIN', 'ROLE_USER' => [$this->stanowisko],
            default => ['ROLE_UNVERIFIED'],
        };
    }

    public function eraseCredentials(): void
    {
        // No sensitive temporary data
    }
    public function setRoles(array $roles): static
{
    if (count($roles) !== 1) {
        throw new \InvalidArgumentException('Użytkownik może mieć tylko jedną rolę.');
    }

    $this->stanowisko = $roles[0];

    return $this;
}
}
