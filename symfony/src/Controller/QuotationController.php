<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Entity\QuoteEquipment;
use App\Repository\QuoteRepository;
use App\Repository\QuoteEquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/quotation')]
class QuotationController extends AbstractController
{
    #[Route('', name: 'get_quotation', methods: ['GET'])]
    public function getQuotation(QuoteRepository $quoteRepository): JsonResponse
    {
        $quotations = $quoteRepository->findAll();
        return $this->json($quotations, 200);
    }

    #[Route('/{id}', name: 'get_quotation_by_id', methods: ['GET'])]
    public function getQuotationById(int $id, QuoteRepository $quoteRepository): JsonResponse
    {
        $quote = $quoteRepository->find($id);
        if (!$quote) {
            return $this->json(['error' => 'Quotation not found'], 404);
        }
        return $this->json($quote, 200);
    }

    #[Route('/equipment', name: 'get_quotation_equipment', methods: ['GET'])]
    public function getQuotationEquipment(QuoteEquipmentRepository $quoteEquipmentRepository): JsonResponse
    {
        $quoteEquipment = $quoteEquipmentRepository->findAll();
        return $this->json($quoteEquipment, 200);
    }

    #[Route('/equipment/{id}', name: 'get_quotation_equipment_by_id', methods: ['GET'])]
    public function getQuotationEquipmentById(int $id, QuoteEquipmentRepository $quoteEquipmentRepository): JsonResponse
    {
        $quoteEquipment = $quoteEquipmentRepository->find($id);
        if (!$quoteEquipment) {
            return $this->json(['error' => 'Item not found in quotation'], 404);
        }
        return $this->json($quoteEquipment, 200);
    }

    #[Route('', name: 'add_quotation', methods: ['POST'])]
    public function addQuotation(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['company'], $data['status'], $data['dane_kontaktowe'], $data['miejsce'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $quote = new Quote();
        $quote->setCompany($data['company']);
        $quote->setStatus($data['status']);
        $quote->setDaneKontaktowe($data['dane_kontaktowe']);
        $quote->setMiejsce($data['miejsce']);
        $quote->setDataWystawienia(new \DateTime());

        $entityManager->persist($quote);
        $entityManager->flush();

        return $this->json(['message' => 'Dodano nową wycenę', 'data' => $quote], 201);
    }

    #[Route('/{id}', name: 'delete_quotation', methods: ['DELETE'])]
    public function deleteQuotation(int $id, QuoteRepository $quoteRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $quote = $quoteRepository->find($id);
        if (!$quote) {
            return $this->json(['error' => 'Quotation not found'], 404);
        }

        $entityManager->remove($quote);
        $entityManager->flush();

        return $this->json(['message' => 'Wycena została usunięta'], 200);
    }
}

