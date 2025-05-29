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


#[Route('/api/upload')]
final class UploadController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    // 1. Endpointy ze stałymi ścieżkami
    #[Route('/tree', name: 'upload_tree', methods: ['GET'])]
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

    // 2. Endpointy z parametrami pośrednimi
    #[Route('/{id<\d+>}/rename', name: 'upload_rename', methods: ['PATCH'])]
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
            // Walidacja: nie można przenieść do siebie ani do potomka
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
    public function status(int $id, UploadRepository $uploadRepository): JsonResponse
    {
        $upload = $uploadRepository->find($id);
        if (!$upload) {
            return $this->json(['error' => 'Not found'], 404);
        }
        return $this->json(['status' => $upload->getStatus()]);
    }

    #[Route('/{id<\d+>}/download', name: 'upload_download', methods: ['GET'])]
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

    // 3. Endpointy z pojedynczym parametrem na końcu
    #[Route('/{id<\d+>}', name: 'upload_details', methods: ['GET'])]
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
}
