<?php

namespace App\Entity;

use App\Repository\QuoteTableEquipmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteTableEquipmentRepository::class)]
class QuoteTableEquipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: QuoteTable::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?QuoteTable $quoteTable = null;

    #[ORM\ManyToOne(targetEntity: Equipment::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Equipment $equipment = null;

    #[ORM\Column]
    private ?int $count = null;

    #[ORM\Column]
    private ?int $days = null;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2)]
    private ?string $discount = null;

    #[ORM\Column(type: "boolean")]
    private bool $showComment = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }
    
    public function getQuoteTable(): ?QuoteTable
    {
        return $this->quoteTable;
    }

    public function setQuoteTable(?QuoteTable $quoteTable): static
    {
        $this->quoteTable = $quoteTable;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): static
    {
        $this->count = $count;

        return $this;
    }

    public function getDays(): ?int
    {
        return $this->days;
    }

    public function setDays(int $days): static
    {
        $this->days = $days;

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

    public function isShowComment(): bool
    {
        return $this->showComment;
    }

    public function setShowComment(bool $showComment): static
    {
        $this->showComment = $showComment;

        return $this;
    }

}
