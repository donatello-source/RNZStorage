<?php

namespace App\Entity;

use App\Repository\QuoteDateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteDateRepository::class)]
class QuoteDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quote::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Quote $quote = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null; // 'single' lub 'range'

    #[ORM\Column(length: 100)]
    private ?string $value = null; 

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
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

    public function getType(): ?string
    {
        return $this->type;
    }
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
    public function getValue(): ?string
    {
        return $this->value;
    }
    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
    public function getComment(): ?string
    {
        return $this->comment;
    }
    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
