<?php

namespace App\Service;

use App\Entity\Quote;
use App\Entity\QuoteDate;
use App\Entity\QuoteTable;
use App\Entity\QuoteTableEquipment;
use App\Entity\Equipment;
use App\Repository\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuotationService
{
    public function __construct(
        private readonly QuoteRepository $quoteRepository,
        private readonly EntityManagerInterface $em
    ) {}

    public function getAllQuotes(): array
    {
        return $this->quoteRepository->findAll();
    }

    public function getQuoteById(int $id): Quote
    {
        $quote = $this->quoteRepository->find($id);
        if (!$quote) {
            throw new NotFoundHttpException('Quotation not found');
        }
        return $quote;
    }

    public function addQuote(array $data): Quote
    {
        if (!isset($data['company_id'], $data['projekt'], $data['lokalizacja'])) {
            throw new BadRequestHttpException('Missing required fields');
        }

        $quote = new Quote();
        $quote->setCompany($data['company_id']);
        $quote->setProjekt($data['projekt']);
        $quote->setLokalizacja($data['lokalizacja']);
        $quote->setGlobalDiscount($data['global_discount'] ?? 0);
        $quote->setStatus('nowa');
        $quote->setDataWystawienia(new \DateTime());

        $this->em->persist($quote);

        // Daty
        if (!empty($data['dates'])) {
            foreach ($data['dates'] as $dateData) {
                $date = new QuoteDate();
                $date->setQuote($quote);
                $date->setType($dateData['type']);
                $date->setValue($dateData['value']);
                $date->setComment($dateData['comment'] ?? null);
                $this->em->persist($date);
            }
        }

        // Tabelki i sprzęt
        if (!empty($data['tables'])) {
            foreach ($data['tables'] as $tableData) {
                $table = new QuoteTable();
                $table->setQuote($quote);
                $table->setLabel($tableData['label']);
                $table->setDiscount($tableData['discount'] ?? 0);
                $this->em->persist($table);

                if (!empty($tableData['items'])) {
                    foreach ($tableData['items'] as $itemData) {
                        $equipment = $this->em->getRepository(Equipment::class)->find($itemData['equipment_id']);
                        if (!$equipment) {
                            throw new BadRequestHttpException('Equipment not found: ' . $itemData['equipment_id']);
                        }
                        $qte = new QuoteTableEquipment();
                        $qte->setQuoteTable($table);
                        $qte->setEquipment($equipment);
                        $qte->setCount($itemData['count']);
                        $qte->setDays($itemData['days']);
                        $qte->setDiscount($itemData['discount'] ?? 0);
                        $qte->setShowComment($itemData['show_comment'] ?? false);
                        $this->em->persist($qte);
                    }
                }
            }
        }

        $this->em->flush();
        return $quote;
    }

    public function deleteQuote(int $id): void
    {
        $quote = $this->quoteRepository->find($id);
        if (!$quote) {
            throw new NotFoundHttpException('Quotation not found');
        }
        $this->em->remove($quote);
        $this->em->flush();
    }
}
