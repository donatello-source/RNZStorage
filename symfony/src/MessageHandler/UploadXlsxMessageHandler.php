<?php

namespace App\MessageHandler;

use App\Message\UploadXlsxMessage;
use App\Repository\UploadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UploadXlsxMessageHandler
{
    private EntityManagerInterface $entityManager;
    private UploadRepository $uploadRepository;

    public function __construct(EntityManagerInterface $entityManager, UploadRepository $uploadRepository)
    {
        $this->entityManager = $entityManager;
        $this->uploadRepository = $uploadRepository;
    }

    public function __invoke(UploadXlsxMessage $message)
    {
        $upload = $this->uploadRepository->find($message->getId());

        if (!$upload) {
            return; // lub loguj błąd
        }

        $upload->setStatus('processing');
        $this->entityManager->flush();

        try {
            // Przetwarzanie pliku XLSX tutaj
            $filePath = $upload->getFilePath();

            // TODO: dodaj logikę parsowania np. PhpSpreadsheet

            // Po sukcesie:
            $upload->setStatus('done');
        } catch (\Throwable $e) {
            $upload->setStatus('error');
            $upload->setErrorMessage($e->getMessage());
        }

        $this->entityManager->flush();
    }
}
