<?php

namespace App\Entity;

use App\Repository\QuoteTableRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteTableRepository::class)]
class QuoteTable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quote::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Quote $quote = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2)]
    private ?string $discount = null;

    #[ORM\OneToMany(mappedBy: 'quoteTable', targetEntity: QuoteTableEquipment::class, orphanRemoval: true)]
    private $equipments;

    public function __construct()
    {
        $this->equipments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }
    
    public function setQuote(?Quote $quote): static
    {
        $this->quote = $quote;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(string $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getEquipments()
    {
        return $this->equipments;
    }
}
