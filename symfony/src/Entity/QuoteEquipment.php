<?php

namespace App\Entity;

use App\Repository\QuoteEquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteEquipmentRepository::class)]
class QuoteEquipment
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Quote::class)]
    #[ORM\JoinColumn(name: "idQuote", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Quote $idQuote;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Equipment::class)]
    #[ORM\JoinColumn(name: "idEquipment", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Equipment $idEquipment;

    #[ORM\Column]
    private ?int $ilosc = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $rabat = null;

    public function getIdQuote(): ?int
    {
        return $this->idQuote;
    }

    public function setIdQuote(int $idQuote): static
    {
        $this->idQuote = $idQuote;

        return $this;
    }

    public function getIdEquipment(): ?int
    {
        return $this->idEquipment;
    }

    public function setIdEquipment(int $idEquipment): static
    {
        $this->idEquipment = $idEquipment;

        return $this;
    }

    public function getIlosc(): ?int
    {
        return $this->ilosc;
    }

    public function setIlosc(int $ilosc): static
    {
        $this->ilosc = $ilosc;

        return $this;
    }

    public function getRabat(): ?string
    {
        return $this->rabat;
    }

    public function setRabat(string $rabat): static
    {
        $this->rabat = $rabat;

        return $this;
    }
}
