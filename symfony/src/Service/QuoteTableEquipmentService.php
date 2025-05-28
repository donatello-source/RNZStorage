<?php

namespace App\Service;

use App\Entity\QuoteTableEquipment;
use App\Entity\QuoteTable;
use App\Entity\Equipment;
use Doctrine\ORM\EntityManagerInterface;

class QuoteTableEquipmentService
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function create(
        QuoteTable $table,
        Equipment $equipment,
        int $count,
        int $days,
        float $discount,
        bool $showComment
    ): QuoteTableEquipment {
        $qte = new QuoteTableEquipment();
        $qte->setQuoteTable($table);
        $qte->setEquipment($equipment);
        $qte->setCount($count);
        $qte->setDays($days);
        $qte->setDiscount($discount);
        $qte->setShowComment($showComment);
        $this->em->persist($qte);
        $this->em->flush();
        return $qte;
    }

    public function find(int $id): ?QuoteTableEquipment
    {
        return $this->em->getRepository(QuoteTableEquipment::class)->find($id);
    }

    public function update(
        QuoteTableEquipment $qte,
        int $count,
        int $days,
        float $discount,
        bool $showComment
    ): QuoteTableEquipment {
        $qte->setCount($count);
        $qte->setDays($days);
        $qte->setDiscount($discount);
        $qte->setShowComment($showComment);
        $this->em->flush();
        return $qte;
    }

    public function delete(QuoteTableEquipment $qte): void
    {
        $this->em->remove($qte);
        $this->em->flush();
    }

    public function findByTable(int $tableId): array
    {
        return $this->em->getRepository(QuoteTableEquipment::class)->findBy(['quoteTable' => $tableId]);
    }
}