<?php

namespace App\MessageHandler;

use App\Message\UploadXlsxMessage;
use App\Repository\UploadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Service\QuoteExportService;
use App\Repository\QuoteRepository;

#[AsMessageHandler]
class UploadXlsxMessageHandler
{
    private EntityManagerInterface $entityManager;
    private UploadRepository $uploadRepository;
    private QuoteExportService $quoteExportService;
    private QuoteRepository $quoteRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UploadRepository $uploadRepository,
        QuoteExportService $quoteExportService,
        QuoteRepository $quoteRepository
    ) {
        $this->entityManager = $entityManager;
        $this->uploadRepository = $uploadRepository;
        $this->quoteExportService = $quoteExportService;
        $this->quoteRepository = $quoteRepository;
    }

    public function __invoke(UploadXlsxMessage $message)
    {
        $upload = $this->uploadRepository->find($message->getId());
        if (!$upload) {
            return;
        }

        $quoteId = $message->getQuoteId();
        if ($quoteId) {
            $quote = $this->quoteRepository->find($quoteId);
            if (!$quote) {
                $upload->setStatus('error');
                $this->entityManager->flush();
                return;
            }
            $this->quoteExportService->generateXlsx($quote, $upload->getFilePath());
        }

        $tmpPath = $message->getFilePath();
        $folderPath = '/var/www/symfony/uploads/';
        $parent = $upload->getParent();
        $parts = [];
        while ($parent) {
            array_unshift($parts, $parent->getOriginalName());
            $parent = $parent->getParent();
        }
        if ($parts) {
            $folderPath .= implode('/', $parts) . '/';
            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
        }

        $finalPath = $folderPath . uniqid() . '_' . $message->getOriginalName();

        if (!rename($tmpPath, $finalPath)) {
            $upload->setStatus('error');
            $this->entityManager->flush();
            return;
        }

        $upload->setFilePath($finalPath);
        $upload->setStatus('done');
        $this->entityManager->flush();
    }
}
