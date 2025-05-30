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

    public function getAllQuotesWithPrices(): array
    {
        $quotes = $this->quoteRepository->findAll();
        $result = [];

        foreach ($quotes as $quote) {
            $netto = 0;
            $quoteTables = $this->em->getRepository(QuoteTable::class)->findBy(['quote' => $quote]);
            foreach ($quoteTables as $table) {
                $tableSum = 0;
                $equipments = $this->em->getRepository(QuoteTableEquipment::class)->findBy(['quoteTable' => $table]);
                foreach ($equipments as $qte) {
                    $equipment = $qte->getEquipment();
                    $price = $equipment->getPrice();
                    $count = $qte->getCount();
                    $days = $qte->getDays();
                    $discount = $qte->getDiscount() ?? 0;
                    $itemSum = $price * $count * $days * (1 - $discount / 100);
                    $tableSum += $itemSum;
                }
                $tableDiscount = $table->getDiscount() ?? 0;
                $tableSum = $tableSum * (1 - $tableDiscount / 100);
                $netto += $tableSum;
            }
            $brutto = $netto * 1.23;
            $result[] = [
                'id' => $quote->getId(),
                'company' => [
                    'id' => $quote->getCompany()?->getId(),
                    'name' => $quote->getCompany()?->getNazwa(),
                ],
                'status' => $quote->getStatus(),
                'dataWystawienia' => $quote->getDataWystawienia()?->format('Y-m-d'),
                'lokalizacja' => $quote->getLokalizacja(),
                'netto' => round($netto, 2),
                'brutto' => round($brutto, 2),
            ];
        }
        return $result;
    }

    public function getQuoteDataForEdit(int $id): array
    {
        $quote = $this->getQuoteById($id);

        $dates = [];
        foreach ($quote->getDates() as $date) {
            $dates[] = [
                'type' => $date->getType(),
                'value' => $date->getValue(),
                'comment' => $date->getComment(),
            ];
        }

        $tables = [];
        foreach ($quote->getTables() as $table) {
            $items = [];
            foreach ($table->getEquipments() as $qte) {
                $equipment = $qte->getEquipment();
                $items[] = [
                    'id' => $equipment->getId(),
                    'name' => $equipment->getName(),
                    'price' => $equipment->getPrice(),
                    'count' => $qte->getCount(),
                    'days' => $qte->getDays(),
                    'discountItem' => $qte->getDiscount(),
                    'showComment' => $qte->isShowComment(),
                    'pricing_info' => $equipment->getPricingInfo(),
                ];
            }
            $tables[] = [
                'label' => $table->getLabel(),
                'discount' => $table->getDiscount(),
                'items' => $items,
            ];
        }

        return [
            'id' => $quote->getId(),
            'company' => $quote->getCompany()?->getId(),
            'projekt' => $quote->getProjekt(),
            'lokalizacja' => $quote->getLokalizacja(),
            'daty' => $dates,
            'tabele' => $tables,
            'rabatCalkowity' => $quote->getGlobalDiscount(),
            'status' => $quote->getStatus(),
            'dataWystawienia' => $quote->getDataWystawienia()?->format('Y-m-d'),
        ];
    }

    public function updateQuote(int $id, array $data): void
    {
        $quote = $this->getQuoteById($id);

        if (isset($data['zamawiajacy'])) {
            $company = $this->em->getRepository(Company::class)->find($data['zamawiajacy']);
            if ($company) {
                $quote->setCompany($company);
            }
        }
        if (isset($data['projekt'])) {
            $quote->setProjekt($data['projekt']);
        }
        if (isset($data['lokalizacja'])) {
            $quote->setLokalizacja($data['lokalizacja']);
        }
        if (isset($data['rabatCalkowity'])) {
            $quote->setGlobalDiscount($data['rabatCalkowity']);
        }

        foreach ($quote->getDates() as $date) {
            $this->em->remove($date);
        }
        foreach ($data['daty'] ?? [] as $dateData) {
            $date = new QuoteDate();
            $date->setQuote($quote);
            $date->setType($dateData['type']);
            $date->setValue($dateData['value']);
            $date->setComment($dateData['comment'] ?? null);
            $this->em->persist($date);
        }

        foreach ($quote->getTables() as $table) {
            foreach ($table->getEquipments() as $qte) {
                $this->em->remove($qte);
            }
            $this->em->remove($table);
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

        $this->em->flush();
    }
}
