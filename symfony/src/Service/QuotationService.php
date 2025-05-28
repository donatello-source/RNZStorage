<?php

namespace App\Service;

use App\Entity\Quote;
use App\Entity\QuoteDate;
use App\Entity\QuoteTable;
use App\Entity\QuoteTableEquipment;
use App\Entity\Equipment;
use App\Repository\QuoteRepository;
use App\Entity\Company;
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
        $company = $this->em->getRepository(Company::class)->find($data['zamawiajacy']);
        if (!$company) {
            throw new BadRequestHttpException('Company not found');
        }

        $quote = new Quote();
        $quote->setCompany($company);
        $quote->setProjekt($data['projekt']);
        $quote->setLokalizacja($data['lokalizacja']);
        $quote->setGlobalDiscount($data['rabatCalkowity'] ?? 0);
        $quote->setStatus('nowa');
        $quote->setDataWystawienia(new \DateTime());

        $this->em->persist($quote);

        foreach ($data['daty'] ?? [] as $dateData) {
            $date = new QuoteDate();
            $date->setQuote($quote);
            $date->setType($dateData['type']);
            $date->setValue($dateData['value']);
            $date->setComment($dateData['comment'] ?? null);
            $this->em->persist($date);
        }

        foreach ($data['tabele'] ?? [] as $tableData) {
            $table = new QuoteTable();
            $table->setQuote($quote);
            $table->setLabel($tableData['kategoria']);
            $table->setDiscount($tableData['rabatTabelki'] ?? 0);
            $this->em->persist($table);

            foreach ($tableData['sprzety'] ?? [] as $itemData) {
                $equipment = $this->em->getRepository(Equipment::class)->find($itemData['id']);
                if (!$equipment) {
                    throw new BadRequestHttpException('Equipment not found: ' . $itemData['id']);
                }
                $qte = new QuoteTableEquipment();
                $qte->setQuoteTable($table);
                $qte->setEquipment($equipment);
                $qte->setCount($itemData['ilosc']);
                $qte->setDays($itemData['dni']);
                $qte->setDiscount($itemData['rabat'] ?? 0);
                $qte->setShowComment($itemData['showComment'] ?? false);
                $this->em->persist($qte);
            }
        }

        $this->em->beginTransaction();
        try {
            $this->em->flush();
            $this->em->commit();
            return $quote;
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
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
