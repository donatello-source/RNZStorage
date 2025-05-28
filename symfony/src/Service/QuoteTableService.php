<?php

namespace App\Service;

use App\Entity\QuoteTable;
use App\Entity\Quote;
use Doctrine\ORM\EntityManagerInterface;

class QuoteTableService
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function create(Quote $quote, string $label, float $discount): QuoteTable
    {
        $table = new QuoteTable();
        $table->setQuote($quote);
        $table->setLabel($label);
        $table->setDiscount($discount);
        $this->em->persist($table);
        $this->em->flush();
        return $table;
    }

    public function find(int $id): ?QuoteTable
    {
        return $this->em->getRepository(QuoteTable::class)->find($id);
    }

    public function update(QuoteTable $table, string $label, float $discount): QuoteTable
    {
        $table->setLabel($label);
        $table->setDiscount($discount);
        $this->em->flush();
        return $table;
    }

    public function delete(QuoteTable $table): void
    {
        $this->em->remove($table);
        $this->em->flush();
    }

    public function findByQuote(int $quoteId): array
    {
        return $this->em->getRepository(QuoteTable::class)->findBy(['quote' => $quoteId]);
    }
}