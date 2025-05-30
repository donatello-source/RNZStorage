<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Repository\UploadRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use OpenApi\Attributes as OA;

#[Route('/api/upload')]
#[OA\Tag(name: 'Upload')]
final class UploadController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    #[Route('/tree', name: 'upload_tree', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz drzewo plików i folderów',
        tags: ['Upload'],
        responses: [
            new OA\Response(response: 200, description: 'Drzewo plików'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function tree(UploadRepository $uploadRepository): JsonResponse
    {
        $uploads = $uploadRepository->findAll();

        $map = [];
        foreach ($uploads as $upload) {
            $map[$upload->getId()] = [
                'id' => $upload->getId(),
                'name' => $upload->getOriginalName(),
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
    #[OA\Post(
        summary: 'Utwórz nowy folder',
        tags: ['Upload'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nowy folder'),
                    new OA\Property(property: 'parent', type: 'integer', example: 1, nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Utworzono folder'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function createFolder(Request $request, EntityManagerInterface $em, UploadRepository $uploadRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $folder = new Upload();
        $folder->setOriginalName($data['name']);
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

    #[Route('/{id<\d+>}/rename', name: 'upload_rename', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Zmień nazwę pliku lub folderu',
        tags: ['Upload'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nowa nazwa')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Zmieniono nazwę'),
            new OA\Response(response: 404, description: 'Nie znaleziono pliku/folderu'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function rename(int $id, Request $request, UploadRepository $uploadRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $upload = $uploadRepository->find($id);
        if (!$upload) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $upload->setOriginalName($data['name']);
        $em->flush();
        return $this->json(['message' => 'Renamed']);
    }

    #[Route('/{id<\d+>}/move', name: 'upload_move', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Przenieś plik lub folder',
        tags: ['Upload'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['parent'],
                properties: [
                    new OA\Property(property: 'parent', type: 'integer', example: 2)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Przeniesiono'),
            new OA\Response(response: 404, description: 'Nie znaleziono pliku/folderu lub folderu docelowego'),
            new OA\Response(response: 400, description: 'Błąd walidacji'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
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
            if ($parent->getId() === $upload->getId()) {
                return $this->json(['error' => 'Nie można przenieść folderu do samego siebie'], 400);
            }
            $ancestor = $parent;
            while ($ancestor) {
                if ($ancestor->getId() === $upload->getId()) {
                    return $this->json(['error' => 'Nie można przenieść folderu do swojego potomka'], 400);
                }
                $ancestor = $ancestor->getParent();
            }
        }
        $upload->setParent($parent);
        $em->flush();
        return $this->json(['message' => 'Moved']);
    }

    #[Route('/{id<\d+>}/children', name: 'upload_folder_children', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz dzieci folderu',
        tags: ['Upload'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista dzieci folderu'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function folderChildren(int $id, UploadRepository $uploadRepository): JsonResponse
    {
        $criteria = $id === 0 ? ['parent' => null] : ['parent' => $id];
        $children = $uploadRepository->findBy($criteria);
        return $this->json(array_map(fn($u) => [
            'id' => $u->getId(),
            'name' => $u->getOriginalName(),
            'type' => $u->getType(),
        ], $children));
    }

    #[Route('/{id<\d+>}/status', name: 'upload_status', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz status pliku',
        tags: ['Upload'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Status pliku'),
            new OA\Response(response: 404, description: 'Nie znaleziono pliku'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function status(int $id, UploadRepository $uploadRepository): JsonResponse
    {
        $upload = $uploadRepository->find($id);
        if (!$upload) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json(['status' => $upload->getStatus()]);
    }

    #[Route('/{id<\d+>}/download', name: 'upload_download', methods: ['GET'])]
    #[OA\Get(
        summary: 'Pobierz plik do pobrania',
        tags: ['Upload'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plik do pobrania'),
            new OA\Response(response: 404, description: 'Nie znaleziono pliku'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function download(int $id, UploadRepository $uploadRepository): JsonResponse
    {
        $upload = $uploadRepository->find($id);
        if (!$upload) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $filePath = $upload->getFilePath();
        $fileName = $upload->getOriginalName();

        return $this->file($filePath, $fileName);
    }

    #[Route('/{id<\d+>}', name: 'upload_details', methods: ['GET'])]
    #[OA\Get(
        summary: 'Szczegóły pliku/folderu',
        tags: ['Upload'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Szczegóły pliku/folderu'),
            new OA\Response(response: 404, description: 'Nie znaleziono pliku/folderu'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
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
            'name' => $upload->getOriginalName(),
            'type' => $upload->getType(),
            'status' => $upload->getStatus() ?? null,
            'parent' => $upload->getParent()?->getId(),
        ]);
    }


    #[Route('/{id<\d+>}', name: 'upload_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Usuń plik lub folder',
        tags: ['Upload'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usunięto plik/folder'),
            new OA\Response(response: 404, description: 'Nie znaleziono pliku/folderu'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function delete(int $id, UploadRepository $uploadRepository, EntityManagerInterface $em): JsonResponse
    {
        $upload = $uploadRepository->find($id);
        if (!$upload) {
            return $this->json(['error' => 'Not found'], 404);
        }
        if ($upload->getType() === 'file') {
            $filePath = $upload->getFilePath();
            if ($filePath && file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $em->remove($upload);
        $em->flush();
        return $this->json(['message' => 'Deleted']);
    }

    #[Route('', name: 'upload_file', methods: ['POST'])]
    #[OA\Post(
        summary: 'Wyślij plik',
        tags: ['Upload'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary'),
                        new OA\Property(property: 'parent', type: 'integer', example: 1, nullable: true)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Plik przesłany'),
            new OA\Response(response: 400, description: 'Brak pliku'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function uploadFile(Request $request, EntityManagerInterface $em, MessageBusInterface $bus): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $parentId = $request->request->get('parent');

        if (!$file) {
            return $this->json(['error' => 'Brak pliku'], 400);
        }

        $tmpPath = '/tmp/uploads/' . uniqid() . '_' . $file->getClientOriginalName();
        $file->move(dirname($tmpPath), basename($tmpPath));

        $upload = new Upload();
        $upload->setStatus('pending');
        $upload->setFilePath($tmpPath);
        $upload->setOriginalName($file->getClientOriginalName());
        $upload->setType('file');
        $em->persist($upload);
        $em->flush();

        $bus->dispatch(new \App\Message\UploadXlsxMessage(
            $upload->getId(),
            $tmpPath,
            $file->getClientOriginalName()
        ));

        return $this->json(['id' => $upload->getId()]);
    }

    #[Route('/generate', name: 'upload_generate_file', methods: ['POST'])]
    #[OA\Post(
        summary: 'Wygeneruj plik na podstawie wyceny',
        tags: ['Upload'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['parent', 'name', 'format', 'quoteId'],
                properties: [
                    new OA\Property(property: 'parent', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'wycena'),
                    new OA\Property(property: 'format', type: 'string', example: 'xlsx'),
                    new OA\Property(property: 'quoteId', type: 'integer', example: 5)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Plik wygenerowany'),
            new OA\Response(response: 400, description: 'Brak wymaganych danych'),
            new OA\Response(response: 401, description: 'Brak autoryzacji')
        ]
    )]
    public function generateFile(
        Request $request,
        EntityManagerInterface $em,
        MessageBusInterface $bus,
        UploadRepository $uploadRepository
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $parent = $data['parent'] ?? null;
        $name = $data['name'] ?? null;
        $format = $data['format'] ?? 'xlsx';
        $quoteId = $data['quoteId'] ?? null;

        if (!$parent || !$name || !in_array($format, ['xlsx', 'numbers'])) {
            return $this->json(['error' => 'Brak wymaganych danych'], 400);
        }

        $parentFolder = $uploadRepository->find($parent);
        if (!$parentFolder || $parentFolder->getType() !== 'folder') {
            return $this->json(['error' => 'Folder docelowy nie istnieje'], 400);
        }

        $ext = $format === 'xlsx' ? '.xlsx' : '.numbers';
        $tmpDir = '/tmp/uploads/';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }
        $tmpPath = $tmpDir . uniqid() . '_' . $name . $ext;
        file_put_contents($tmpPath, '');

        $upload = new Upload();
        $upload->setStatus('pending');
        $upload->setFilePath($tmpPath);
        $upload->setOriginalName($name . $ext);
        $upload->setType('file');
        $upload->setParent($parentFolder);
        $em->persist($upload);
        $em->flush();

        $bus->dispatch(new \App\Message\UploadXlsxMessage(
            $upload->getId(),
            $tmpPath,
            $name . $ext,
            $quoteId
        ));

        return $this->json(['id' => $upload->getId()]);
    }
}
