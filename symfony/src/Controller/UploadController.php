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

    #[Route('/tree', name: 'upload_tree', methods: ['GET'])]
    public function tree(UploadRepository $uploadRepository): JsonResponse
    {
        $uploads = $uploadRepository->findAll();

        $map = [];
        foreach ($uploads as $upload) {
            $map[$upload->getId()] = [
                'id' => $upload->getId(),
                'name' => $upload->getName(),
                'type' => $upload->getType(),
                'status' => $upload->getStatus() ?? null,
                'parent' => $upload->getParent()?->getId(),
                'children' => [],
            ];
        }

        $tree = [];
        foreach ($map as $id => &$node) {
            if ($node['parent']) {
                $map[$node['parent']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        unset($node);

        return $this->json($tree);
    }

    #[Route('/folder', name: 'upload_create_folder', methods: ['POST'])]
    public function createFolder(Request $request, EntityManagerInterface $em, UploadRepository $uploadRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $folder = new Upload();
        $folder->setName($data['name']);
        $folder->setType('folder');
        if (!empty($data['parent'])) {
            $parent = $uploadRepository->find($data['parent']);
            if ($parent) {
                $folder->setParent($parent);
            }
        }
        $em->persist($folder);
        $em->flush();
        return $this->json(['id' => $folder->getId()]);
    }

    #[Route('/{id}/rename', name: 'upload_rename', methods: ['PATCH'])]
    public function rename(int $id, Request $request, UploadRepository $uploadRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $upload = $uploadRepository->find($id);
        if (!$upload) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $upload->setName($data['name']);
        $em->flush();
        return $this->json(['message' => 'Renamed']);
    }

    #[Route('/{id}/move', name: 'upload_move', methods: ['PATCH'])]
    public function move(int $id, Request $request, UploadRepository $uploadRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $upload = $uploadRepository->find($id);
        if (!$upload) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $parent = null;
        if (!empty($data['parent'])) {
            $parent = $uploadRepository->find($data['parent']);
            if (!$parent) {
                return $this->json(['error' => 'Parent not found'], 404);
            }
        }
        $upload->setParent($parent);
        $em->flush();
        return $this->json(['message' => 'Moved']);
    }

    #[Route('/{id}', name: 'upload_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuwa plik lub folder',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono')
        ]
    )]
    public function delete(int $id, UploadRepository $uploadRepository, EntityManagerInterface $em): JsonResponse
    {
        $upload = $uploadRepository->find($id);
        if (!$upload) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $em->remove($upload);
        $em->flush();
        return $this->json(['message' => 'Deleted']);
    }

    #[Route('/{id}', name: 'upload_details', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz szczegóły pliku lub folderu',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Szczegóły pliku/folderu',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'type', type: 'string'),
                        new OA\Property(property: 'status', type: 'string', nullable: true),
                        new OA\Property(property: 'parent', type: 'integer', nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono')
        ]
    )]
    public function details(int $id, UploadRepository $uploadRepository): JsonResponse
    {
        $upload = $uploadRepository->find($id);
        if (!$upload) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json([
            'id' => $upload->getId(),
            'name' => $upload->getName(),
            'type' => $upload->getType(),
            'status' => $upload->getStatus() ?? null,
            'parent' => $upload->getParent()?->getId(),
        ]);
    }

    #[Route('/{id}/children', name: 'upload_children', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz zawartość folderu',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista plików/folderów',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'type', type: 'string'),
                            new OA\Property(property: 'status', type: 'string', nullable: true)
                        ]
                    )
                )
            )
        ]
    )]
    public function children(int $id, UploadRepository $uploadRepository): JsonResponse
    {
        $children = $uploadRepository->findBy(['parent' => $id]);
        return $this->json(array_map(fn($u) => [
            'id' => $u->getId(),
            'name' => $u->getName(),
            'type' => $u->getType(),
            'status' => $u->getStatus() ?? null,
        ], $children));
    }
}
