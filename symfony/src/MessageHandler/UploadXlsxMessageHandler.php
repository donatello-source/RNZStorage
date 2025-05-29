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
            return;
        }

        $tmpPath = $message->getFilePath();
        $finalPath = '/var/www/symfony/uploads/' . uniqid() . '_' . $message->getOriginalName();

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
