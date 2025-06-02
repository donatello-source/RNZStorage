<?php

namespace App\Service;

use App\Entity\Upload;
use App\Repository\UploadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;


readonly class UploadService
{
    private SluggerInterface $slugger;
    private UploadRepository $uploadRepository;
    private EntityManagerInterface $entityManager;
    private string $uploadDir;

    public function __construct(UploadRepository $uploadRepository, EntityManagerInterface $entityManager, string $uploadDir, SluggerInterface $slugger)
    {
        $this->uploadRepository = $uploadRepository;
        $this->entityManager = $entityManager;
        $this->uploadDir = $uploadDir;
        $this->slugger = $slugger;
    }

    public function store(UploadedFile $file): Upload
    {
        $originalName = $file->getClientOriginalName();
        $safeFilename = $this->slugger->slug(pathinfo($originalName, PATHINFO_FILENAME));
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $uploadDir = __DIR__ . '/../../var/uploads';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($uploadDir)) {
            $filesystem->mkdir($uploadDir, 0777);
        }

        $filePath = $uploadDir . '/' . $newFilename;
        $file->move($uploadDir, $newFilename);

        $upload = new Upload();
        $upload->setFilePath($filePath);
        $upload->setOriginalName($originalName);
        $upload->setStatus('pending');
        $upload->setCreatedAt(new \DateTime());
        $upload->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($upload);
        $this->entityManager->flush();

        return $upload;
    }
    public function getAll(): array
    {
        $uploads = $this->uploadRepository->findBy([], ['createdAt' => 'DESC']);

        return array_map(function (Upload $upload) {
            return [
                'id' => $upload->getId(),
                'filepath' => $upload->getFilePath(),
                'original_name' => $upload->getOriginalName(),
                'status' => $upload->getStatus(),
                'created_at' => $upload->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'errormessage' => $upload->getErrorMessage(),
                'download_url' => $upload->getStatus() === 'done'
                    ? "/api/upload/{$upload->getId()}/download"
                    : null
            ];
        }, $uploads);
    }

    public function getStatus(int $id): ?array
    {
        $upload = $this->uploadRepository->find($id);
        if (!$upload) {
            return null;
        }

        return [
            'status' => $upload->getStatus(),
            'progress' => method_exists($upload, 'getProgress') ? $upload->getProgress() : null,
            'error' => $upload->getErrorMessage(),
        ];
    }

    public function getFilePath(int $id): ?string
    {
        $upload = $this->uploadRepository->find($id);
        if (!$upload || $upload->getStatus() !== 'done') {
            return null;
        }

        $filePath = $this->targetDirectory . '/' . $upload->getFilename();

        return file_exists($filePath) ? $filePath : null;
    }


}
