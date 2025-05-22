<?php

namespace App\Controller;

use App\Service\UploadService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\UploadXlsxMessage;
use OpenApi\Attributes as OA;

#[Route('/api/upload')]
final class UploadController extends AbstractController
{
    public function __construct(
        private readonly UploadService $uploadService,
        private readonly MessageBusInterface $messageBus
    ) {}

    #[Route('', name: 'upload_file', methods: ['POST'])]
    #[OA\Post(
        summary: 'Dodaje plik XLSX do kolejki przetwarzania',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['file'],
                    properties: [
                        new OA\Property(
                            property: 'file',
                            type: 'string',
                            format: 'binary'
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Plik dodany do kolejki'),
            new OA\Response(response: 400, description: 'Błędne dane lub brak pliku')
        ]
    )]
    #[OA\Tag(name: 'Upload')]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file) {
            throw new BadRequestHttpException('Plik nie został przesłany');
        }

        try {
            $upload = $this->uploadService->store($file);
            $this->messageBus->dispatch(new UploadXlsxMessage(
                $upload->getId(),
                $upload->getFilePath(),
                $upload->getOriginalName()
            ));
        } catch (FileException $e) {
            throw new BadRequestHttpException('Błąd zapisu pliku: ' . $e->getMessage());
        }

        return $this->json(['message' => 'Plik dodany do kolejki', 'uploadId' => $upload->getId()], 201);
    }
    
    #[Route('', name: 'upload_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->uploadService->getAll());
    }

    #[Route('/{id}/status', name: 'upload_status', methods: ['GET'])]
    public function status(int $id): JsonResponse
    {
        $status = $this->uploadService->getStatus($id);
        if (!$status) {
            throw new NotFoundHttpException('Plik nie znaleziony');
        }

        return $this->json($status);
    }

    #[Route('/{id}/download', name: 'upload_download', methods: ['GET'])]
    public function download(int $id): Response
    {
        $file = $this->uploadService->getFilePath($id);
        if (!$file) {
            throw new NotFoundHttpException('Plik nie istnieje lub nie jest gotowy');
        }

        return $this->file($file, basename($file), ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

}
