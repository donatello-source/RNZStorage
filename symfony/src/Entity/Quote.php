<?php

namespace App\Entity;

use App\Repository\QuoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
class Quote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $company = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $dodatkoweInformacje = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dataWystawienia = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dataPoczatek = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dataKoniec = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $daneKontaktowe = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $miejsce = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $rabat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCompany(): ?int
    {
        return $this->company;
    }

    public function setCompany(int $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getDodatkoweInformacje(): ?string
    {
        return $this->dodatkoweInformacje;
    }

    public function setDodatkoweInformacje(?string $dodatkoweInformacje): static
    {
        $this->dodatkoweInformacje = $dodatkoweInformacje;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDataWystawienia(): ?\DateTimeImmutable
    {
        return $this->dataWystawienia;
    }

    public function setDataWystawienia(\DateTimeImmutable $dataWystawienia): static
    {
        $this->dataWystawienia = $dataWystawienia;

        return $this;
    }

    public function getDataPoczatek(): ?\DateTimeInterface
    {
        return $this->dataPoczatek;
    }

    public function setDataPoczatek(\DateTimeInterface $dataPoczatek): static
    {
        $this->dataPoczatek = $dataPoczatek;

        return $this;
    }

    public function getDataKoniec(): ?string
    {
        return $this->dataKoniec;
    }

    public function setDataKoniec(\DateTimeInterface $dataKoniec): static
    {
        $this->dataKoniec = $dataKoniec;

        return $this;
    }

    public function getDaneKontaktowe(): ?string
    {
        return $this->daneKontaktowe;
    }

    public function setDaneKontaktowe(string $daneKontaktowe): static
    {
        $this->daneKontaktowe = $daneKontaktowe;

        return $this;
    }

    public function getMiejsce(): ?string
    {
        return $this->miejsce;
    }

    public function setMiejsce(string $miejsce): static
    {
        $this->miejsce = $miejsce;

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
