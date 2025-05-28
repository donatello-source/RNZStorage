<?php

namespace App\Service;

use App\Entity\QuoteDate;
use App\Entity\Quote;
use Doctrine\ORM\EntityManagerInterface;

class QuoteDateService
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function create(Quote $quote, string $type, string $value, ?string $comment): QuoteDate
    {
        $date = new QuoteDate();
        $date->setQuote($quote);
        $date->setType($type);
        $date->setValue($value);
        $date->setComment($comment);
        $this->em->persist($date);
        $this->em->flush();
        return $date;
    }

    public function find(int $id): ?QuoteDate
    {
        return $this->em->getRepository(QuoteDate::class)->find($id);
    }

    public function update(QuoteDate $date, string $type, string $value, ?string $comment): QuoteDate
    {
        $date->setType($type);
        $date->setValue($value);
        $date->setComment($comment);
        $this->em->flush();
        return $date;
    }

    public function delete(QuoteDate $date): void
    {
        $this->em->remove($date);
        $this->em->flush();
    }

    public function findByQuote(int $quoteId): array
    {
        return $this->em->getRepository(QuoteDate::class)->findBy(['quote' => $quoteId]);
    }
}