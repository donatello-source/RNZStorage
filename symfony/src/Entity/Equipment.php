<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idsprzet", type: "integer")]
    private ?int $id = null;
    
    #[ORM\Column(name: "nazwa", type: "string", length: 100)]
    private ?string $name = null;

    #[ORM\Column(name: "opis", type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: "ilosc", type: "integer")]
    private int $quantity;

    #[ORM\Column(name: "cena", type: "decimal", precision: 10, scale: 2)]
    private float $price;

    #[ORM\Column(name: "idkategoria", type: "integer", nullable: true)]
    private ?int $categoryId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): static
    {
        $this->categoryId = $categoryId;
        return $this;
    }
}
