<?php

namespace App\Service;

use App\Entity\Quote;
use App\Repository\QuoteRepository;
use App\Repository\QuoteEquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuotationService
{
    public function __construct(
        private readonly QuoteRepository $quoteRepository,
        private readonly QuoteEquipmentRepository $quoteEquipmentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function getAllQuotes(): array
    {
        return $this->quoteRepository->findAll();
    }

    public function getQuoteById(int $id): ?Quote
    {
        return $this->quoteRepository->find($id);
    }

    public function getAllQuoteEquipment(): array
    {
        return $this->quoteEquipmentRepository->findAll();
    }

    public function getQuoteEquipmentById(int $id): mixed
    {
        return $this->quoteEquipmentRepository->find($id);
    }

    public function addQuote(array $data): array
    {
        if (!isset(
            $data['company'],
            $data['status'],
            $data['dane_kontaktowe'],
            $data['miejsce'],
            $data['data_wystawienia'],
            $data['data_poczatek'],
            $data['data_koniec']
        )) {
            return ['error' => 'Missing required fields', 'status' => 400];
        }

        try {
            $quote = new Quote();
            $quote->setCompany($data['company']);
            $quote->setStatus($data['status']);
            $quote->setDaneKontaktowe($data['dane_kontaktowe']);
            $quote->setMiejsce($data['miejsce']);
            $quote->setDataWystawienia(new \DateTime($data['data_wystawienia']));
            $quote->setDataPoczatek(new \DateTime($data['data_poczatek']));
            $quote->setDataKoniec(new \DateTime($data['data_koniec']));

            $this->entityManager->persist($quote);
            $this->entityManager->flush();

            return ['data' => $quote, 'status' => 201];
        } catch (\Exception $e) {
            return ['error' => 'Invalid date or other data', 'status' => 400];
        }
    }

    public function deleteQuote(int $id): array
    {
        $quote = $this->quoteRepository->find($id);
        if (!$quote) {
            return ['error' => 'Quotation not found', 'status' => 404];
        }

        $this->entityManager->remove($quote);
        $this->entityManager->flush();

        return ['message' => 'Wycena została usunięta', 'status' => 200];
    }
}
