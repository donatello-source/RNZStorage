<?php

namespace App\Service;

use App\Entity\Quote;
use App\Repository\QuoteRepository;
use App\Repository\QuoteEquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function getQuoteById(int $id): Quote
    {
        $quote = $this->quoteRepository->find($id);
        if (!$quote) {
            throw new NotFoundHttpException('Quotation not found');
        }
        return $quote;
    }

    public function getAllQuoteEquipment(): array
    {
        return $this->quoteEquipmentRepository->findAll();
    }

    public function getQuoteEquipmentById(int $id): object
    {
        $equipment = $this->quoteEquipmentRepository->find($id);
        if (!$equipment) {
            throw new NotFoundHttpException('Item not found in quotation');
        }
        return $equipment;
    }

    public function addQuote(array $data): Quote
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
            throw new BadRequestHttpException('Missing required fields');
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

            return $quote;
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Invalid date or other data');
        }
    }

    public function deleteQuote(int $id): void
    {
        $quote = $this->quoteRepository->find($id);
        if (!$quote) {
            throw new NotFoundHttpException('Quotation not found');
        }

        $this->entityManager->remove($quote);
        $this->entityManager->flush();
    }
}
