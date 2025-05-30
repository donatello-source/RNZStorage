<?php

namespace App\Entity;

use App\Repository\QuoteRepository;
use App\Entity\Company;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
class Quote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(length: 255)]
    private ?string $projekt = null;

    #[ORM\Column(length: 255)]
    private ?string $lokalizacja = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $globalDiscount = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dataWystawienia = null;

    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: QuoteDate::class, orphanRemoval: true)]
    private $dates;

    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: QuoteTable::class, orphanRemoval: true)]
    private $tables;

    public function __construct()
    {
        $this->dates = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tables = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getProjekt(): ?string
    {
        return $this->projekt;
    }

    public function setProjekt(string $projekt): static
    {
        $this->projekt = $projekt;
        return $this;
    }

    public function getLokalizacja(): ?string
    {
        return $this->lokalizacja;
    }

    public function setLokalizacja(string $lokalizacja): static
    {
        $this->lokalizacja = $lokalizacja;
        return $this;
    }

    public function getGlobalDiscount(): ?string
    {
        return $this->globalDiscount;
    }

    public function setGlobalDiscount(string $globalDiscount): static
    {
        $this->globalDiscount = $globalDiscount;
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

    public function getDataWystawienia(): ?\DateTimeInterface
    {
        return $this->dataWystawienia;
    }

    public function setDataWystawienia(\DateTimeInterface $dataWystawienia): static
    {
        $this->dataWystawienia = $dataWystawienia;
        return $this;
    }

    public function getDates()
    {
        return $this->dates;
    }

    public function getTables()
    {
        return $this->tables;
    }
}
